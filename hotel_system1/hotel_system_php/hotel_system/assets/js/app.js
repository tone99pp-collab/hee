document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('menuToggleBtn');
    const sidebar = document.getElementById('sidebarNav');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    document.querySelectorAll('.toggle-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('off');
        });
    });
});

function copyBankDetails(text) {
    if (!navigator.clipboard) {
        alert('ຄັດລອກຂໍ້ມູນ: ' + text);
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        alert('ຄັດລອກຂໍ້ມູນບັນຊີທະນາຄານສຳເລັດແລ້ວ!');
    }).catch(() => {
        alert('ຂໍ້ມູນບັນຊີ: ' + text);
    });
}
