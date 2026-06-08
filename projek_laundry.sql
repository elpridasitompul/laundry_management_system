//  tabel admin
CREATE TABLE admin (
    id_admin NUMBER PRIMARY KEY,
    username VARCHAR2(50) UNIQUE NOT NULL,
    password VARCHAR2(100) NOT NULL,
    nama_admin VARCHAR2(50) NOT NULL
);

// tabel pelanggan
CREATE TABLE pelanggan (
    id_pelanggan NUMBER PRIMARY KEY,
    nama_pelanggan VARCHAR2(100) NOT NULL,
    alamat VARCHAR2(150) NOT NULL,
    no_hp VARCHAR2(15) NOT NULL
);

//  tabel layanan
CREATE TABLE layanan (
    id_layanan NUMBER PRIMARY KEY,
    nama_layanan VARCHAR2(50) NOT NULL,
    harga_per_kg NUMBER NOT NULL
);

//  tabel transaksi
CREATE TABLE transaksi (
    id_transaksi NUMBER PRIMARY KEY,
    id_pelanggan NUMBER NOT NULL,
    id_admin NUMBER NOT NULL,
    kode_transaksi VARCHAR2(30) UNIQUE NOT NULL,
    tanggal_masuk DATE NOT NULL,
    tanggal_selesai DATE,
    status VARCHAR2(20) NOT NULL,
    total_bayar NUMBER,
    metode_pembayaran VARCHAR2(20),
    status_pembayaran VARCHAR2(20) ,

    CONSTRAINT fk_trans_pelanggan
        FOREIGN KEY (id_pelanggan)
        REFERENCES pelanggan(id_pelanggan),

    CONSTRAINT fk_trans_admin
        FOREIGN KEY (id_admin)
        REFERENCES admin(id_admin)
);

//  tabel detail transaksi
CREATE TABLE detail_transaksi (
    id_detail NUMBER PRIMARY KEY,
    id_transaksi NUMBER NOT NULL,
    id_layanan NUMBER NOT NULL,
    berat NUMBER NOT NULL,
    subtotal NUMBER,

    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (id_transaksi)
        REFERENCES transaksi(id_transaksi),

    CONSTRAINT fk_detail_layanan
        FOREIGN KEY (id_layanan)
        REFERENCES layanan(id_layanan)
);

//  tabel pengeluaran
CREATE TABLE pengeluaran (
    id_pengeluaran NUMBER PRIMARY KEY,
     tanggal DATE NOT NULL,
    keterangan VARCHAR2(200) NOT NULL,
    jumlah NUMBER NOT NULL
);

//  auto increment

CREATE SEQUENCE seq_admin START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_pelanggan START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_layanan START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_transaksi START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_detail START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE seq_pengeluaran START WITH 1 INCREMENT BY 1;


INSERT INTO admin 
VALUES (seq_admin.NEXTVAL, 'admin', '123', 'Admin');
select *from admin;


CREATE USER laundry IDENTIFIED BY 123;

GRANT CONNECT, RESOURCE TO laundry;

desc layanan;





