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
class Export extends EA_Controller
{
    /**
     * Calendar constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('appointments_model');
        $this->load->model('unavailabilities_model');
        $this->load->model('blocked_periods_model');
        $this->load->model('customers_model');
        $this->load->model('services_model');
        $this->load->model('providers_model');
        $this->load->model('roles_model');

        $this->load->library('accounts');
        $this->load->library('google_sync');
        $this->load->library('notifications');
        $this->load->library('synchronization');
        $this->load->library('timezones');
        $this->load->library('webhooks_client');

    }

    /**
     * Render the settings page.
     */
    public function index(): void
    {
        session(['dest_url' => site_url('calendar/export')]);

        $user_id = session('user_id');

        if (cannot('view', PRIV_SYSTEM_SETTINGS)) {
            if ($user_id) {
                abort(403, 'Forbidden');
            }

            redirect('login');

            return;
        }

        $role_slug = session('role_slug');

        $user = $this->users_model->find($user_id);

        $secretary_providers = [];

        if ($role_slug === DB_SLUG_SECRETARY) {
            $secretary = $this->secretaries_model->find(session('user_id'));

            $secretary_providers = $secretary['providers'];
        }

        $edit_appointment = null;

        //$all = $this->get_calendar_appointments_for_table_view()->to_array();
        //$all = json_encode($this->xxx());
        //$all = json_encode('noch nicht');
 
        $start_date = date("Y-m-d") . ' 00:00:00';
        $end_date   = date("Y-m-d") . ' 23:59:59';
        $all = $this->get_calendar_appointments_for_table_view($start_date,$end_date);
        script_vars([
            'user_id' => $all,
        ]);
        html_vars([
            'user_id' => $all,
        ]);

        $this->load->view('pages/export');
    }

    public function get_calendar_appointments_for_table_view($start_date,$end_date)
    {
        try {
            $required_permissions = can('view', PRIV_APPOINTMENTS);

            if (!$required_permissions) {
                throw new RuntimeException('You do not have the required permissions for this task.');
            }

            //$start_date = request('start_date') . ' 00:00:00';

            //$end_date = request('end_date') . ' 23:59:59';

            $response = [
                'appointments' => $this->appointments_model->get([
                    'start_datetime >=' => $start_date,
                    'end_datetime <=' => $end_date,
                ]),
            ];

            foreach ($response['appointments'] as &$appointment) {
                $appointment['provider'] = $this->providers_model->find($appointment['id_users_provider']);
                $appointment['service'] = $this->services_model->find($appointment['id_services']);
                $appointment['customer'] = $this->customers_model->find($appointment['id_users_customer']);
            }

            unset($appointment);

            $user_id = session('user_id');

            $role_slug = session('role_slug');

            // If the current user is a provider he must only see his own appointments.
            if ($role_slug === DB_SLUG_PROVIDER) {
                foreach ($response['appointments'] as $index => $appointment) {
                    if ((int) $appointment['id_users_provider'] !== (int) $user_id) {
                        unset($response['appointments'][$index]);
                    }
                }

                $response['appointments'] = array_values($response['appointments']);

            }

            // If the current user is a secretary he must only see the appointments of his providers.
            if ($role_slug === DB_SLUG_SECRETARY) {
                $providers = $this->secretaries_model->find($user_id)['providers'];

                foreach ($response['appointments'] as $index => $appointment) {
                    if (!in_array((int) $appointment['id_users_provider'], $providers)) {
                        unset($response['appointments'][$index]);
                    }
                }

                $response['appointments'] = array_values($response['appointments']);

            }


            unset($unavailability);

            return $response;

        } catch (Throwable $e) {
            return json_exception($e);
        }
    }

 





}
