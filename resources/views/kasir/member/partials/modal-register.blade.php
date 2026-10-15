<x-modals.modal id="modal-register-member" title="Pendaftaran Member Baru" subtitle="Daftarkan pelanggan menjadi member untuk program loyalitas" maxWidth="max-w-md">
    <form method="POST" action="{{ route('members.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

        <x-forms.input name="name" label="Nama Lengkap" placeholder="Contoh: Budi Santoso" required />

        <x-forms.input name="phone" label="Nomor WhatsApp / HP" placeholder="081234567890" required />

        <x-forms.input type="email" name="email" label="Email (Opsional)" placeholder="customer@example.com" />

        <div class="pt-3 border-t border-[#E4E7EC] flex justify-end gap-3">
            <x-buttons.btn variant="outline" type="button" data-modal-close="modal-register-member">
                Batal
            </x-buttons.btn>
            <x-buttons.btn variant="primary" type="submit">
                Daftarkan Member
            </x-buttons.btn>
        </div>
    </form>
</x-modals.modal>
