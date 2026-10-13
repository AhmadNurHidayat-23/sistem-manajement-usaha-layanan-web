const path = require('path');
const fs = require('fs');
const express = require('express');
const qrcode = require('qrcode-terminal');
const pino = require('pino');
const {
  default: makeWASocket,
  DisconnectReason,
  useMultiFileAuthState,
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.PORT || 3001;

app.use(express.json());

// State koneksi gateway
let sock = null;
let isConnected = false;
let latestQr = null;

/**
 * Normalisasi nomor telepon ke format internasional 62xxx
 */
function normalizePhone(phone) {
  if (!phone) return null;
  const cleaned = String(phone).replace(/\D/g, '');
  if (!cleaned) return null;

  if (cleaned.startsWith('62')) {
    return cleaned;
  }
  if (cleaned.startsWith('0')) {
    return '62' + cleaned.slice(1);
  }
  if (cleaned.startsWith('8')) {
    return '62' + cleaned;
  }

  return cleaned;
}

/**
 * Endpoint Health & Status Check
 */
app.get('/health', (req, res) => {
  res.json({
    status: isConnected ? 'connected' : 'disconnected',
    connected: isConnected,
    hasQr: Boolean(latestQr),
  });
});

/**
 * Endpoint Raw QR Code
 */
app.get('/qr-raw', (req, res) => {
  res.json({
    connected: isConnected,
    qr: latestQr,
  });
});

/**
 * Endpoint Web View QR Code untuk memudahkan scan via browser
 */
app.get('/qr', (req, res) => {
  if (isConnected) {
    return res.send(`
      <!DOCTYPE html>
      <html>
      <head><title>SIMAUL WhatsApp Gateway - Terhubung</title></head>
      <body style="font-family: sans-serif; text-align: center; padding: 40px; background: #F7F9FC;">
        <div style="max-width: 420px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #E4E7EC;">
          <h2 style="color: #062072; margin-top: 0;">WhatsApp Terhubung!</h2>
          <p style="color: #16A34A; font-weight: bold; font-size: 16px;">Status: Online & Siap Digunakan</p>
          <p style="color: #667085; font-size: 13px;">Gateway aktif pada port ${PORT}. Pesan notifikasi SIMAUL akan otomatis terkirim.</p>
        </div>
      </body>
      </html>
    `);
  }

  if (!latestQr) {
    return res.send(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>SIMAUL WhatsApp Gateway - Memuat QR</title>
        <meta http-equiv="refresh" content="3">
      </head>
      <body style="font-family: sans-serif; text-align: center; padding: 40px; background: #F7F9FC;">
        <div style="max-width: 420px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #E4E7EC;">
          <h2 style="color: #062072; margin-top: 0;">Menyiapkan WhatsApp Gateway...</h2>
          <p style="color: #667085; font-size: 14px;">Menghubungkan ke server WhatsApp dan menghasilkan QR code. Halaman akan me-refresh otomatis.</p>
        </div>
      </body>
      </html>
    `);
  }

  res.send(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Scan WhatsApp SIMAUL</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    </head>
    <body style="font-family: sans-serif; text-align: center; padding: 30px 15px; background: #F7F9FC;">
      <div style="max-width: 440px; margin: 0 auto; background: white; padding: 25px; border-radius: 16px; box-shadow: 0 6px 16px rgba(0,0,0,0.08); border: 1px solid #E4E7EC;">
        <h2 style="color: #062072; margin-top: 0; margin-bottom: 8px;">Scan QR WhatsApp SIMAUL</h2>
        <p style="color: #475467; font-size: 13px; margin-bottom: 20px;">
          Buka WhatsApp di HP &rarr; <b>Perangkat Tertaut</b> &rarr; <b>Tautkan Perangkat</b>
        </p>
        <div id="qrcode" style="display: inline-block; padding: 12px; background: #ffffff; border: 1px solid #D0D5DD; border-radius: 12px;"></div>
        <p style="color: #98A2B3; font-size: 11px; margin-top: 15px;">QR otomatis diperbarui jika belum tersambung.</p>
      </div>
      <script>
        new QRCode(document.getElementById("qrcode"), {
          text: ${JSON.stringify(latestQr)},
          width: 256,
          height: 256,
          correctLevel: QRCode.CorrectLevel.M
        });
        setInterval(async () => {
          try {
            const res = await fetch('/health');
            const data = await res.json();
            if (data.connected) {
              window.location.reload();
            }
          } catch(e) {}
        }, 3000);
      </script>
    </body>
    </html>
  `);
});

/**
 * Endpoint Pengiriman Pesan WhatsApp
 */
app.post('/send-message', async (req, res) => {
  const { phone, message } = req.body;

  if (!phone || !message) {
    return res.status(400).json({
      success: false,
      error: 'Nomor telepon (phone) dan pesan (message) wajib diisi.',
    });
  }

  const normalized = normalizePhone(phone);
  if (!normalized) {
    return res.status(400).json({
      success: false,
      error: 'Nomor telepon tidak valid.',
    });
  }

  if (!isConnected || !sock) {
    return res.status(503).json({
      success: false,
      error: 'WhatsApp client belum terhubung. Silakan scan QR code terlebih dahulu.',
    });
  }

  try {
    const jid = `${normalized}@s.whatsapp.net`;
    const result = await sock.sendMessage(jid, { text: String(message) });
    const messageId = result?.key?.id || null;

    return res.json({
      success: true,
      messageId: messageId,
      phone: normalized,
    });
  } catch (error) {
    console.error('[Baileys] Gagal mengirim pesan:', error?.message);
    return res.status(500).json({
      success: false,
      error: error?.message || 'Gagal mengirim pesan via WhatsApp',
    });
  }
});

/**
 * Inisialisasi koneksi Baileys WhatsApp
 */
async function connectToWhatsApp() {
  const sessionDir = path.join(__dirname, '../sessions');
  if (!fs.existsSync(sessionDir)) {
    fs.mkdirSync(sessionDir, { recursive: true });
  }

  try {
    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    sock = makeWASocket({
      auth: state,
      printQRInTerminal: false,
      logger: pino({ level: 'silent' }),
      browser: ['SIMAUL Gateway', 'Chrome', '1.0.0'],
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
      const { connection, lastDisconnect, qr } = update;

      if (qr) {
        latestQr = qr;
        console.log('\n==================================================');
        console.log('SCAN QR CODE DI BAWAH DENGAN WHATSAPP:');
        console.log('==================================================\n');
        qrcode.generate(qr, { small: true });
        console.log('\n(Buka WhatsApp > Perangkat Tertaut > Tautkan Perangkat)');
        console.log('Atau buka di browser: http://127.0.0.1:' + PORT + '/qr\n');
      }

      if (connection === 'close') {
        isConnected = false;
        const statusCode = lastDisconnect?.error?.output?.statusCode;
        const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

        console.log(`[Baileys] Koneksi terputus (Status: ${statusCode || 'unknown'}).`);

        if (shouldReconnect) {
          console.log('[Baileys] Menghubungkan ulang dalam 5 detik...');
          setTimeout(connectToWhatsApp, 5000);
        } else {
          console.log('[Baileys] Sesi telah logout. Membersihkan sesi lama...');
          try {
            fs.rmSync(sessionDir, { recursive: true, force: true });
          } catch (e) {}
          setTimeout(connectToWhatsApp, 3000);
        }
      } else if (connection === 'open') {
        isConnected = true;
        latestQr = null;
        console.log('\n[Baileys] WhatsApp Gateway BERHASIL TERHUBUNG!\n');
      }
    });
  } catch (err) {
    console.error('[Baileys] Inisialisasi socket gagal:', err?.message);
    setTimeout(connectToWhatsApp, 5000);
  }
}

// Jalankan HTTP server dan koneksi Baileys
app.listen(PORT, () => {
  console.log(`[WhatsApp Gateway] Server berjalan pada http://127.0.0.1:${PORT}`);
  connectToWhatsApp();
});
