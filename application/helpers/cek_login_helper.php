<?php
defined('BASEPATH') or exit('No direct script access allowed');
function cek_login_user()
{
	$ci = get_instance();
	if (!$ci->session->userdata('login')) {
		redirect('login');
	}
}
function urlPath()
{
	$url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$url_seqgemnt = explode('/', $url_path);

	return $url_seqgemnt[2];
}
