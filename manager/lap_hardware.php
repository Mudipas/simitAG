<?php
require('kop_hardware.php');

 
function GenerateWord()
{
	//Get a random word
	$nb=rand(3,10);
	$w='';
	for($i=1;$i<=$nb;$i++)
		$w.=chr(rand(ord('a'),ord('z')));
	return $w;
}

function GenerateSentence()
{
	//Get a random sentence
	$nb=rand(1,10);
	$s='';
	for($i=1;$i<=$nb;$i++)
		$s.=GenerateWord().' ';
	return substr($s,0,-1);
}

function selisihHari($tglAwal, $tglAkhir){
// list tanggal merah selain hari minggu
    $tglLibur = Array("2023-04-07","2023-04-20","2023-04-21", "2023-08-25", "2023-05-18");
	
	// $tglAwal = "16-08-2021";
	// $tglAkhir = "18-08-2021";
     
    // memecah string tanggal awal untuk mendapatkan
    // tanggal, bulan, tahun
    $pecah1 = explode("-", $tglAwal);
    $date1 = $pecah1[0];
    $month1 = $pecah1[1];
    $year1 = $pecah1[2];
	$tanggalGabungan1 = $date1."-".$month1."-".$year1;
    // memecah string tanggal akhir untuk mendapatkan
    // tanggal, bulan, tahun
    $pecah2 = explode("-", $tglAkhir);
    $date2 = $pecah2[0];
    $month2 = $pecah2[1];
    $year2 =  $pecah2[2];
 
    // mencari total selisih hari dari tanggal awal dan akhir
    $jd1 = GregorianToJD($month1, $date1, $year1);
    $jd2 = GregorianToJD($month2, $date2, $year2);
 
    $selisih = $jd2 - $jd1;
     
    // proses menghitung tanggal merah dan hari minggu
    // di antara tanggal awal dan akhir
    for($i=1; $i<=$selisih; $i++)
    {
        // menentukan tanggal pada hari ke-i dari tanggal awal
        $tanggal = mktime(0, 0, 0, $month1, $date1+$i, $year1);
        $tglstr = date("Y-m-d", $tanggal);
         
        // menghitung jumlah tanggal pada hari ke-i
        // yang masuk dalam daftar tanggal merah selain minggu
        if (in_array($tglstr, $tglLibur)) 
        {
           $libur1++;
        }
         
        // menghitung jumlah tanggal pada hari ke-i
        // yang merupakan hari minggu
        if ((date("N", $tanggal) == 7))
        {
           $libur2++;
        }
		if ((date("N", $tanggal) == 6))
        {
           $libur3++;
        }
		//$hari = date("N", $tanggal) == 7;
		
    }
     
    // menghitung selisih hari yang bukan tanggal merah dan hari minggu
    $selisihTotal=$selisih-$libur1-$libur2-$libur3; 
	
	if($selisihTotal == 1){

		$jselisihTotal = $selisihTotal;
    }
	if($selisihTotal== 0){

		$selisihTotal = 1;
	}
	return $selisihTotal;
}


$pdf=new PDF ('L');
$pdf->AddPage();
$pdf->SetFont('Arial','',8);
//Table with 20 rows and 5 columns
//$pdf->SetWidths(array(20,20,50,30,60,20,45,15,15));
$pdf->SetWidths(array(7,17,19,40,30,50,20,30,25,20,20));
//srand(microtime()*1000000);

//koneksi ke database
mysql_connect("localhost","root","dlris30g");
mysql_select_db("sitag");

$status=$_POST['status'];
$bln_akhir=$_POST['bln_akhir'];
$thn_akhir=$_POST['thn_akhir'];
$tanggal_akhir=$thn_akhir.$bln_akhir.$tgl_akhir;
$tanggal_akhir_format=$bln_akhir."-".$thn_akhir;


//mengambil data dari tabel
$sql=mysql_query("SELECT * from service,divisi where service.divisi=divisi.kd and statup='service' and service.status='Selesai' and service.ket='D' and  service.tgl like '%".$tanggal_akhir_format."' and divisi = 'A' order by service.tgl asc");
$count=mysql_num_rows($sql);
$no = 0;
for($i=0;$i<$count;$i++);{
while ($database = mysql_fetch_array($sql)) {
	$no++;
$tgl=$database['tgl'];
$nama=$database['nama'];
$bagian=$database['bagian'];
$barang=$database['barang'];
$tgl2=$database['tgl2'];
$divisi=$database['namadivisi'];
$kasus=$database['kasus'];
$penerima=$database['penerima'];
$teknisi=$database['teknisi'];
$tindakan=$database['tindakan'];
$status=$database['status'];
$ippc=$database['ippc'];
$perangkat=$database['perangkat'];
$awal=substr($tgl,0,2);
$akhir=substr($tgl2,0,2);
$semua=$hbulan;
$bulan_awal=substr($tgl,3,2);
$bulan_akhir=substr($tgl2,3,2);
$total_bulan=$bulan_akhir-$bulan_awal;
$hbulan=$total_bulan*30+$akhir-$awal;
$namabesar=strtoupper($nama);
$perangkatbesar=strtoupper($perangkat);
$kasusbesar=strtoupper($kasus);
$penerimabesar=strtoupper($penerima);
$statusbesar=strtoupper($status);
$teknisibesar=strtoupper($teknisi);
$tglRequest = $database['tglRequest'];
$kategori = $database['svc_kat'];
if($hbulan == 0){
$hbulan=1;	
}
$durasi = 0;
if($tglRequest == ""|| $tglRequest == null){
	$durasi = 0;
}else{
	$durasi = selisihHari($tglRequest,$tgl);
	//$durasi = 2;
}
if($kategori == "NON_SP"){
	$kategori_text = "NON SPAREPART";
}else if ($kategori == "SP"){
	$kategori_text = "SPAREPART";
}else {
	$kategori_text = "-";
}
//$pdf->Row(array($tglRequest, $tgl,$namabesar."\n".$bagian."-".$divisi,$perangkatbesar,$kasusbesar,$penerimabesar,$statusbesar."-".$teknisibesar."(".$tgl2.")",$hbulan." hr", $hbulan." hr"));
$pdf->Row(array($no,$tglRequest, $tgl,$namabesar."\n".$bagian."-".$divisi,$perangkatbesar,$kasusbesar,$penerimabesar,$statusbesar."-".$teknisibesar."(".$tgl2.")", $kategori_text,selisihHari($tgl,$tgl2)." hr", $durasi." hr"));
}}
$pdf->Output();
?>
