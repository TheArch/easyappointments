<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.5.0
 * ---------------------------------------------------------------------------- */

/**
 * General settings controller.
 *
 * Handles general settings related operations.
 *
 * @package Controllers
 */
class Getdata extends EA_Controller
{
    /**
     * Calendar constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->library('accounts');

    }

    /**
     * Render the settings page.
     */
    public function index(): void
    {
        session(['dest_url' => site_url('booking/getdata')]);

        $user_id = session('user_id');
        $this->load->view('pages/getdata');
    }

}
