import Swal from 'sweetalert2';

export const AlvoraSwal = Swal.mixin({
    confirmButtonColor: '#2563EB',
    cancelButtonColor: '#64748B',
    customClass: {
        popup: 'font-sans text-sm rounded-2xl shadow-xl border border-slate-100',
        confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold shadow-xs',
        cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold shadow-xs',
    },
    buttonsStyling: true,
});

if (typeof window !== 'undefined') {
    window.AlvoraSwal = AlvoraSwal;
}

export default AlvoraSwal;

