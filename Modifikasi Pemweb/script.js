// Ambil data lama kalau ada
let dataPengajuan = JSON.parse(localStorage.getItem("pengajuan")) || [];

const form = document.getElementById("formPengajuan");

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
      alasan: document.getElementById("alasan").value,
    };

    // 1. VALIDASI KOLOM KOSONG
    for (const key in data) {
      if (data[key].trim() === "") {
        alert("Maaf, semua kolom data diri dan pengajuan wajib diisi!");
        return;
      }
    }

    // 2. VALIDASI NIK (Wajib 16 digit angka)
    if (data.nik.length !== 16 || isNaN(data.nik)) {
      alert("NIK tidak valid! Harap masukkan tepat 16 digit angka.");
      return;
    }

    // 3. VALIDASI NOMOR HP (Wajib angka, panjang 10-13 digit)
    if (isNaN(data.hp) || data.hp.length < 10 || data.hp.length > 13) {
      alert(
        "Nomor HP tidak valid! Harap masukkan angka antara 10 hingga 13 digit.",
      );
      return;
    }

    dataPengajuan.push(data);

    localStorage.setItem("pengajuan", JSON.stringify(dataPengajuan));

    alert("Pengajuan berhasil disimpan!");

    form.reset();
  });
}
