/**
 * Penambahan chip keahlian (skill) ke input teks karyawan.
 */
export function addSkillChip(skill) {
    const input = document.getElementById('skills');
    if (!input) return;

    const current = input.value.split(',').map(s => s.trim()).filter(s => s !== '');
    if (!current.includes(skill)) {
        current.push(skill);
        input.value = current.join(', ');
    }
}

if (typeof window !== 'undefined') {
    window.addSkillChip = addSkillChip;
}
