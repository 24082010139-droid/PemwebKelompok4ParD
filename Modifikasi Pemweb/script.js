// Ambil data lama kalau ada
let dataPengajuan = JSON.parse(localStorage.getItem("pengajuan")) || [];

const form = document.getElementById("formBantuan");

if (form) {
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const data = {
      nama: document.getElementById("nama").value,
      nik: document.getElementById("nik").value,
      hp: document.getElementById("hp").value,
      provinsi: document.getElementById("provinsi").value,
      kota: document.getElementById("kota").value,
      kecamatan: document.getElementById("kecamatan").value,
      desa: document.getElementById("desa").value,
      jenis: document.getElementById("jenis").value,
      alasan: document.getElementById("alasan").value
    };

    // Validasi tambahan JS
    if (data.nik.length !== 16 || isNaN(data.nik)) {
      alert("NIK harus 16 digit angka!");
      return;
    }

    dataPengajuan.push(data);

    localStorage.setItem("pengajuan", JSON.stringify(dataPengajuan));

    alert("Pengajuan berhasil disimpan!");

    form.reset();
  });
}