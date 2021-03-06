<?php

date_default_timezone_set('Asia/Jakarta');

defined('BASEPATH') or exit('No direct script access allowed');

class Siswa extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('session');
		$this->load->model('M_Setting');
		$this->load->model('M_Siswa');
		$this->load->model('M_Provinsi');
		$this->load->model('M_Kota');
		$this->load->model('M_Kecamatan');
		$this->load->model('M_Kelas');
		$this->load->model('M_Akses');
		
		$this->load->library(array('PHPExcel', 'PHPExcel/IOFactory'));
		// $this->load->library('ImportExcel'); //load librari excel
		cek_login_user();
	}

	public function index()
	{
		$id = $this->session->userdata('tipeuser');
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['datasiswa'] = $this->M_Siswa->getsiswa();
		$data['datalulus'] = $this->M_Siswa->getLulus();		
		$data['akses'] = $this->M_Akses->getByLinkSubMenu(urlPath(), $id);
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;

		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa', $data);
		$this->load->view('template/footer');
	}

	public function siswa_detail($nis)
	{
		$id = $this->session->userdata('tipeuser');
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['datasiswa'] = $this->M_Siswa->getsiswadetail($nis);
		$data['akses'] = $this->M_Akses->getByLinkSubMenu(urlPathDet(), $id);
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;

		$this->load->view('template/sidebar', $data);
		$this->load->view('template/header');
		$this->load->view('v_siswa/v_siswa-detail', $data);
		$this->load->view('template/footer');
		// print_r($this->M_Siswa->getsiswadetail($nis));
	}

	public function siswa_add()
	{
		$id = $this->session->userdata('tipeuser');
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['prov'] = $this->M_Provinsi->getprovinsi();
		$data['kelas'] = $this->M_Kelas->getkelas();
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;

		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa-add', $data);
		$this->load->view('template/footer');
	}

	public function getKota($idProv)
	{
		$data = $this->M_Kota->getkotadetail($idProv);

		echo json_encode($data);
	}

	public function getSiswaByKelas($idKelas)
	{
		$data = $this->M_Kelas->getSiswaByKelas($idKelas);

		echo json_encode($data);
	}

	public function getKecamatan($idkota)
	{
		$data = $this->M_Kecamatan->getkecadetail($idkota);

		echo json_encode($data);
	}

	public function add_process()
	{
		if ($this->M_Siswa->cekNis($this->input->post('nis', true))) {
			if ($this->M_Siswa->cekRfid($this->input->post('rfid', true))) {
				$nis = $this->input->post('nis', true);
				$nama = $this->input->post('nama', true);
				$alamat = $this->input->post('alamat', true);
				$jk = $this->input->post('jk', true);
				$kelas = $this->input->post('kelas', true);
				$prov = $this->input->post('prov', true);
				$kota = $this->input->post('kota', true);
				$kecamatan = $this->input->post('kecamatan', true);
				$rfid = $this->input->post('rfid', true);
				$tmp_lahir = $this->input->post('tempat_lahir', true);
				$tgl_lahir = $this->input->post('tanggal_lahir', true);

				$id_tipeuser = $this->db->get_where('tb_tipeuser', ['tipeuser' => 'siswa'])->row_array();
				
				if(count($id_tipeuser) === 1 || $id_tipeuser !== null){
					$data = array(
						'nis' => $nis,
						'namasiswa' => $nama,
						'alamat' => $alamat,
						'tempat_lahir' => $tmp_lahir,
						'tgl_lahir' => $tgl_lahir,
						'provinsi' => $prov,
						'kota' => $kota,
						'kecamatan' => $kecamatan,
						'jk' => $jk,
						'id_kelas' => $kelas,
						'tgl_update' => date("Y-m-d h:i:sa"),
						'id_user' => $this->session->userdata('id_user'),
						'status' => 'aktif',
						'id_tipeuser' => $id_tipeuser['id_tipeuser'],
						'password' => 'siswa123',
						'rfid' => $rfid
					);
	
					$this->M_Siswa->addSiswa($data);
					$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
																<strong>Sukses!</strong> Berhasil Menambahkan Data Siswa.
															</div>');
					redirect(base_url('siswa/'));
				}else{
					$this->session->set_flashdata('alert', '<div class="alert alert-danger left-icon-alert" role="alert">
		                                            		<strong>Gagal!</strong> Mohon tambahkan Tipe User siswa terlebih dulu.
		                                        		</div>');
					redirect(base_url('siswa/'));
				}
			} else {
				$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
		                                            		<strong>Perhatian!</strong> RFID sudah ada, Coba lagi.
		                                        		</div>');
				redirect(base_url('siswa/'));
			}
		} else {
			$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
	                                            		<strong>Perhatian!</strong> NIS sudah ada, Coba lagi.
	                                        		</div>');
			redirect(base_url('siswa/'));
		}
	}

	public function siswa_delete($nis)
	{
		$this->M_Siswa->delSiswa($nis);
		$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
                                            		<strong>Sukses!</strong> Berhasil Menghapus Data Siswa.
                                        		</div>');
		redirect(base_url('siswa/'));
	}

	public function siswa_edit($nis)
	{
		$id = $this->session->userdata('tipeuser');
		$data['datasiswa'] = $this->M_Siswa->getsiswadetail($nis);
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['prov'] = $this->M_Provinsi->getprovinsi();
		$data['kelas'] = $this->M_Kelas->getkelas();
		$data['kota'] = $this->M_Kota->getkotadetail($this->M_Siswa->getsiswadetail($nis)['provinsi']);
		$data['keca'] = $this->M_Kecamatan->getkecadetail($this->M_Siswa->getsiswadetail($nis)['kota']);
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;

		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa-edit', $data);
		$this->load->view('template/footer');
	}

	public function edt_process()
	{
		// echo $this->input->post('alumni');

		if ($this->input->post('alumni') == 1) {
			$status = 'alumni';
		} else {
			if ($this->input->post('status') === 'alumni') {
				$status = 'aktif';
			} else {
				$status = 'aktif';
			}
		}

		$data = array(
			// 'nis' => $this->input->post('nis', true),
			'namasiswa' => $this->input->post('nama', true),
			'alamat' => $this->input->post('alamat', true),
			'tempat_lahir' => $this->input->post('tempat_lahir', true),
			'tgl_lahir' => $this->input->post('tanggal_lahir', true),
			'provinsi' => $this->input->post('prov', true),
			'kota' => $this->input->post('kota', true),
			'kecamatan' => $this->input->post('kecamatan', true),
			'jk' => $this->input->post('jk', true),
			'id_kelas' => $this->input->post('kelas', true),
			'tgl_update' => date("Y-m-d h:i:sa"),
			'status' => $status,
			// 'rfid' => $this->input->post('rfid', true)
		);

		if ($this->input->post('nisOld') === $this->input->post('nis')) {
			$data['nis'] = $this->input->post('nis', true);
			if ($this->input->post('rfidOld') === $this->input->post('rfid')) {
				$data['rfid'] = $this->input->post('rfid', true);
				$this->M_Siswa->editSiswa($data, $this->input->post('nisOld'));
				$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
                                    		<strong>Sukses!</strong> Berhasil Mengubah Data Siswa.
                                		</div>');
				redirect(base_url('siswa/'));
			} else {
				if ($this->db->get_where('tb_siswa', ['rfid' => $this->input->post('rfid')])->num_rows() == 0) {
					$data['rfid'] = $this->input->post('rfid', true);
					$this->M_Siswa->editSiswa($data, $this->input->post('nisOld'));
					$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
                                        		<strong>Sukses!</strong> Berhasil Mengubah Data Siswa.
                                    		</div>');
					redirect(base_url('siswa/'));
				} else {
					$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
                                        		<strong>Perhatian!</strong> RFID sudah ada 2.
                                    		</div>');
					redirect(base_url('siswa/'));
				}
			}
		} else {
			if ($this->M_Siswa->cekNis($this->input->post('nis'))) {
				$data['nis'] = $this->input->post('nis', true);
				if ($this->input->post('rfidOld') === $this->input->post('rfid')) {
					$data['rfid'] = $this->input->post('rfid', true);
					$this->M_Siswa->editSiswa($data, $this->input->post('nisOld'));
					$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
                                        		<strong>Sukses!</strong> Berhasil Mengubah Data Siswa.
                                    		</div>');
					redirect(base_url('siswa/'));
				} else {
					if ($this->db->get_where('tb_siswa', ['rfid' => $this->input->post('rfid')])->num_rows() != 0) {
						$data['rfid'] = $this->input->post('rfid', true);
						$this->M_Siswa->editSiswa($data, $this->input->post('nisOld'));
						$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
                                            		<strong>Sukses!</strong> Berhasil Mengubah Data Siswa.
                                        		</div>');
						redirect(base_url('siswa/'));
					} else {
						$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
                                            		<strong>Perhatian!</strong> RFID sudah ada 1.
                                        		</div>');
						redirect(base_url('siswa/'));
					}
				}
			} else {
				$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
                                            		<strong>Perhatian!</strong> NIS sudah ada.
                                        		</div>');
				redirect(base_url('siswa/'));
			}
		}
	}

	public function siswa_graduate()
	{
		$data['datalulus'] = $this->M_Siswa->getLulus();
		$id = $this->session->userdata('tipeuser');
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['kelas'] = $this->M_Kelas->getkelas();
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa lulus'])->row()->id_menus;
		$data['akses'] = $this->M_Akses->getByLinkSubMenu(urlPath(), $id);
		
		
		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa-graduate', $data);
		$this->load->view('template/footer');
	}
	
	public function grad_process($id)
	{
		$data = ['status' => 'alumni'];
		$this->db->where('id_kelas', $id);		
		if($this->db->update('tb_siswa', $data)){
			echo 'berhasil';
		}else{
			echo 'salah';
		}
	}
	
	public function siswa_export()
	{
		$id = $this->session->userdata('tipeuser');
		
		$data['akses'] = $this->M_Akses->getByLinkSubMenu(urlPath(), $id);
		$data['datasiswa'] = $this->M_Siswa->getsiswa();
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['kelas'] = $this->M_Kelas->getkelas();
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;
		
		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa-export', $data);
		$this->load->view('template/footer');
	}
	
	public function export_process($idKelas)
	{
		$data['data'] = $this->M_Kelas->getSiswaByKelas($idKelas);
		$data['kelas'] = $this->db->get_where('tb_kelas', ['id_kelas' => $idKelas])->row();
		$this->load->view('v_siswa/v_siswa-export_page', $data);
	}
	
	public function siswa_import()
	{
		$id = $this->session->userdata('tipeuser');
		
		$data['akses'] = $this->M_Akses->getByLinkSubMenu(urlPath(), $id);
		$data['datasiswa'] = $this->M_Siswa->getsiswa();
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['kelas'] = $this->M_Kelas->getkelas();
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;

		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa-import', $data);
		$this->load->view('template/footer');
	}

	public function upload()
	{
		$fileName = time() . $_FILES['file']['name'];

		$config['upload_path'] = './assets/excel/'; //buat folder dengan nama assets di root folder
		$config['file_name'] = str_replace(" ", "", $fileName);
		$config['allowed_types'] = 'xls|xlsx|csv';
		$config['max_size'] = 10000;

		$this->load->library('upload');
		$this->upload->initialize($config);

		if (!$this->upload->do_upload('file'))
			$this->upload->display_errors();

		$media = $this->upload->data('file');
		$inputFileName = './assets/excel/' . $config['file_name'];

		try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch (Exception $e) {
			redirect('siswa-import');
		}

		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$data = [];
		$dataKosong = [];
		$no = 0;
		$kosong = 0;
		// var_dump($highestRow);
		// var_dump($highestColumn);
		$id_tipeuser = $this->db->get_where('tb_tipeuser', ['tipeuser' => 'siswa'])->row_array();
		if(6 <= $highestRow){
			if(count($id_tipeuser) === 1 || $id_tipeuser !== null){
				for ($row = 6; $row < $highestRow; $row++) {                  //  Read a row of data into an array                 
					$rowData = $sheet->rangeToArray(
						'A' . $row . ':' . $highestColumn . $row,
						NULL,
						TRUE,
						FALSE
					);
	
					//Sesuaikan sama nama kolom tabel di database     
					if( empty($rowData[0][1]) || empty($rowData[0][2]) || empty($rowData[0][3]) || empty($rowData[0][4]) || empty($rowData[0][6])){

						// $this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
						// 								<strong>Perhatian!</strong> Ada data anda yang kosong, Tolong cek kembali.
						// 							</div>');
						// redirect(base_url('siswa-import/'));
						if(empty($rowData[0][1]) && empty($rowData[0][2]) && empty($rowData[0][3]) && empty($rowData[0][4]) && empty($rowData[0][6])){
							// $kosong++;
						}else{
							$dataKosong[$no++] = array(
								"nis" => $rowData[0][1],
								"namasiswa" => $rowData[0][2],
								'jk' => $rowData[0][3],
								'id_kelas' => $rowData[0][4],
								'tempat_tgl_lahir' => $rowData[0][5],								
								'alamat' => $rowData[0][6],
								// 'id_kelas' => $rowData[0][10],
								// 'tgl_update' => date("Y-m-d h:i:sa"),
								// 'id_user' => $this->session->userdata('id_user'),
								// 'status' => 'aktif',
								// 'id_tipeuser' => $id_tipeuser['id_tipeuser'],
								// 'password' => 'siswa123',
							);
						}
															
					}else{
						// $date = strtotime(PHPExcel_Style_NumberFormat::toFormattedString($rowData[0][5], 'YYYY-MM-DD'));
						if($this->db->get_where('tb_kelas', ['kelas LIKE' => '%'. $rowData[0][4].'%' ])->num_rows() != 0){
							$data[$no++] = array(
								"nis" => $rowData[0][1],
								"namasiswa" => $rowData[0][2],
								'jk' => $this->M_Siswa->getJK($rowData[0][3]),
								'id_kelas' => $this->db->get_where('tb_kelas', ['kelas LIKE' => '%'. $rowData[0][4].'%' ])->row()->id_kelas,
								'tempat_tgl_lahir' => $rowData[0][5],
								'alamat' => $rowData[0][6],
								'tgl_lahir' => (!empty($rowData[0][5]) ? explode(',', $rowData[0][5])[1] : '' ),
								'tempat_lahir' => (!empty($rowData[0][5]) ? explode(',', $rowData[0][5])[0] : '' ),
								// 'kecamatan' => $this->db->get_where('tb_kecamatan', ['kecamatan LIKE' => '%'.$rowData[0][6].'%' ])->row()->id_kecamatan,
								// 'kota' => $this->db->get_where('tb_kota', ['name_kota LIKE' => '%' . $rowData[0][7] . '%'])->row()->id_kota,
								// 'provinsi' => $this->db->get_where('tb_provinsi', ['name_prov LIKE' => '%' . $rowData[0][8] . '%'])->row()->id_provinsi,
								// 'id_kelas' => $rowData[0][10],
								'tgl_update' => date("Y-m-d h:i:sa"),
								'id_user' => $this->session->userdata('id_user'),
								'status' => 'aktif',
								'id_tipeuser' => $id_tipeuser['id_tipeuser'],
								'password' => 'siswa123',
							);
						}else{
							$this->session->set_flashdata('alert', '<div class="alert alert-danger left-icon-alert" role="alert">
														<strong>Gagal!</strong> Kelas '.$rowData[0][4].' Tambah kan Terlebih dulu
													</div>');
							redirect(base_url('siswa/'));
						}
					}
				}
			}else{
				$this->session->set_flashdata('alert', '<div class="alert alert-danger left-icon-alert" role="alert">
														<strong>Gagal!</strong> Mohon tambahkan Tipe User siswa terlebih dulu.
													</div>');
				redirect(base_url('siswa/'));
			}
		}else{
			$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
														<strong>Perhatian!</strong> File excel anda kosong.
													</div>');
			redirect(base_url('siswa-import/'));
		}
		$id = $this->session->userdata('tipeuser');
		$this->session->dataImport = $data;
		// $this->session->dataKosongImport = $data;

		// echo count($dataKosong);
		// echo count($data);
		// echo count($kosong);
		if(count($dataKosong) !== 0){
			$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
													<strong>Perhatian!</strong> Ada data anda yang kosong, Tolong cek kembali dan Upload Kembali.
												</div>');
					redirect(base_url('siswa-import/'));
		}else{
			$datas['datasiswa'] = $this->session->dataImport;
			$datas['menu'] = $this->M_Setting->getmenu1($id);
			$datas['kelas'] = $this->M_Kelas->getkelas();
			$datas['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa'])->row()->id_menus;

			$this->load->view('template/header');
			$this->load->view('template/sidebar', $datas);
			$this->load->view('v_siswa/v_siswa-import_page', $datas);
			$this->load->view('template/footer');
		}		
	}

	public function import()
	{
		$data = $this->session->dataImport;
		$dataRow = 0;
		for ($i = 0; $i < count($data); $i++) {
			unset($data[$i]['tempat_tgl_lahir']);
			if($this->db->get_where('tb_siswa',['nis' => $data[$i]['nis']])->num_rows() === 0){
				$this->db->insert('tb_siswa', $data[$i]);
				$this->session->set_flashdata('alert', '<div class="alert alert-success left-icon-alert" role="alert">
				<strong>Sukses!</strong> Berhasil Import Data Siswa.
				</div>');
			}else{
				$dataRow = $dataRow + 1;
				$this->session->set_flashdata('alert', '<div class="alert alert-warning left-icon-alert" role="alert">
				<strong>Perhatian!</strong> Ada '.$dataRow.' Data Siswa Yang Sudah Ada Dalam Database.
				</div>');
			}
		}
		// $this->session->unset_tempdata('dataImport');		
		redirect('siswa');
	}

	public function getSiswa(){
		echo json_encode($this->M_Siswa->getsiswa());
	}

	public function downloadTMP($kelas){
		$data['kelas'] = $this->db->get_where('tb_kelas', ['id_kelas' => $kelas])->row()->kelas;
		$this->load->view('v_siswa/v_siswa-download-tmp', $data);
	}
	public function graduate_page(){
		$id = $this->session->userdata('tipeuser');
		$data['menu'] = $this->M_Setting->getmenu1($id);
		$data['kelas'] = $this->M_Kelas->getkelas();
		$data['activeMenu'] = $this->db->get_where('tb_submenu', ['submenu' => 'siswa lulus'])->row()->id_menus;
		$data['akses'] = $this->M_Akses->getByLinkSubMenu(urlPath(), $id);

		$this->load->view('template/header');
		$this->load->view('template/sidebar', $data);
		$this->load->view('v_siswa/v_siswa-graduate-page', $data);
		$this->load->view('template/footer');
	}

	public function getSiswaSrch($key){
		$this->db->where('nis LIKE', '%'.$key.'%')->or_where('namasiswa LIKE', '%'.$key.'%');
		echo json_encode($this->db->get_where('v_siswa', ['status' => 'aktif'])->result());
	}

	public function gradByOne($id)
	{
		if($this->M_Siswa->siswaGraduate($id)){
			echo 'berhasil';
		}else{
			echo 'gagal';
		}
		
	}
}
