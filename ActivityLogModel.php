<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ActivityLogModel extends CI_Model {
    public $parent_table = '';
    public $log_table = '';
    public $action_name_created = '';
    public $action_name_added = '';
    public $action_name_changed = '';
    public $action_name_deleted = '';
    public $columns_to_ignore = array();
    public $action_by = '';
    public $action_date = '';
    public $action_host = '';

    public function __construct() {
        parent::__construct();
        $this->parent_table = 'users';
        $this->log_table = 'activity_logs';
        $this->action_name_created = 'created';
        $this->action_name_added = 'added';
        $this->action_name_changed = 'changed';
        $this->action_name_deleted = 'deleted';
        $this->columns_to_ignore = array();
        $this->action_by = '';
        $this->action_date = '';
        $this->action_host = '';

    }

    public function insertion_logs($data_i,$table_index,$primary_column,$multiple_index = -1,$action_extras = -1)
    {
        if($multiple_index > -1)
        {
            $new = array();
            foreach ($data_i[$table_index][$multiple_index][0] as $key => $row) {
                if(in_array($key,$this->columns_to_ignore) || $key == $primary_column){continue;}
                $new[$key] = $data_i[$table_index][$multiple_index][0][$key];
            }
            $primary_value = $data_i[$table_index][$multiple_index][0][$primary_column];
            unset($data_i[$table_index][$multiple_index]);
            $data_i[$table_index][$multiple_index] = array('db_changes' => $new, 'primary' => array( 'column_name' => $primary_column, 'column_value' => $primary_value, 'action_extras' => $action_extras ));
        }
        else
        {
            $new = array();
            foreach ($data_i[$table_index][0] as $key => $row) {
                if(in_array($key,$this->columns_to_ignore) || $key == $primary_column){continue;}
                $new[$key] = $data_i[$table_index][0][$key];
            }
            $primary_value = $data_i[$table_index][0][$primary_column];
            unset($data_i[$table_index]);
            $data_i[$table_index] = array('db_changes' => $new, 'primary' => array( 'column_name' => $primary_column, 'column_value' => $primary_value, 'action_extras' => $action_extras ));
        }
        return $data_i;
    }

    public function deletion_logs($data_d,$table_index,$primary_column,$multiple_index = -1,$action_extras = -1)
    {
        if($multiple_index > -1)
        {
            if($action_extras != -1)
            {
                $action_extras = $data_d[$table_index][$multiple_index][0][$action_extras];
            }
            $primary_value = $data_d[$table_index][$multiple_index][0][$primary_column];
            unset($data_d[$table_index][$multiple_index]);
            $data_d[$table_index][$multiple_index] = array('primary' => array( 'column_name' => $primary_column, 'column_value' => $primary_value, 'action_extras' => $action_extras ));
        }
        else
        {
            if($action_extras != -1)
            {
                $action_extras = $data_d[$table_index][0][$action_extras];
            }
            $primary_value = $data_d[$table_index][0][$primary_column];
            unset($data_d[$table_index]);
            $data_d[$table_index] = array('primary' => array( 'column_name' => $primary_column, 'column_value' => $primary_value, 'action_extras' => $action_extras ));
        }
        return $data_d;
    }

    public function updation_logs($data_u,$table_index,$primary_column,$multiple_index = -1,$action_extras = -1)
    {
        $this->columns_to_ignore = explode(',',COLUMNS_TO_IGNORE_FOR_LOGS);
        if($multiple_index > -1)
        {
            $changes = array();
            foreach ($data_u[$table_index][$multiple_index][0] as $key => $row) {
                if(in_array($key,$this->columns_to_ignore)){continue;}
                if($data_u[$table_index][$multiple_index][0][$key] != $data_u[$table_index][$multiple_index][1][$key])
                {
                    $changes[$key] = array(
                        'old' => $data_u[$table_index][$multiple_index][0][$key],
                        'new' => $data_u[$table_index][$multiple_index][1][$key]
                    );
                }
            }
            $primary_value = $data_u[$table_index][$multiple_index][0][$primary_column];
            unset($data_u[$table_index][$multiple_index]);
            if(!empty($changes)){
                $data_u[$table_index][$multiple_index] = array('db_changes' => $changes, 'primary' => array( 'column_name' => $primary_column, 'column_value' => $primary_value, 'action_extras' => $action_extras ));
            }
        }
        else
        {
            $changes = array();
            foreach ($data_u[$table_index][0] as $key => $row) {
                if(in_array($key,$this->columns_to_ignore)){continue;}
                if($data_u[$table_index][0][$key] != $data_u[$table_index][1][$key])
                {
                    $changes[$key] = array(
                        'old' => $data_u[$table_index][0][$key],
                        'new' => $data_u[$table_index][1][$key]
                    );
                }
            }
            $primary_value = $data_u[$table_index][0][$primary_column];
            unset($data_u[$table_index]);
            if(!empty($changes))
            {
                $data_u[$table_index] = array('db_changes' => $changes, 'primary' => array( 'column_name' => $primary_column, 'column_value' => $primary_value, 'action_extras' => $action_extras ));
            }
        }
        return $data_u;
    }
    
    function create_activity_log($data = array(),$parent_id = -1,$action_extras = -1){
        $data_to_insert = array(
            'action_table'          => '', 
            'action_column'         => '', 
            'action'                => '', 
            'action_value'          => '', 
            'action_value_old'      => '',
            'action_extras'         => '-1',
            'extras'                => $parent_id
        );
        foreach($data as $key => $row)
        {
            $data_to_insert[$key] = $row;
        }
        $data_to_insert['action_by'] = $this->action_by ;
        $data_to_insert['action_date'] = $this->action_date ;
        $data_to_insert['action_host'] = $this->action_host ;
        $this->db->insert($this->log_table, $data_to_insert);
    }

    function save_activity_logs($case, $data_i, $data_u, $data_d, $parent_id)
    {
        $is_new = (!empty($data_i)) && empty($data_u) && empty($data_d);
        switch ($case) {
            case '1':
                if($this->parent_table != '' && ($is_new && isset($data_i[$this->parent_table])))
                {
                    $this->create_activity_log(array(
                        'action'                 => $this->action_name_created, 
                        'action_table'           => $this->parent_table, 
                        'action_extras'          => $data_i[$this->parent_table]['primary']['action_extras']
                    ),$parent_id);
                }
                else
                {
                    /* All your insertions kept here */
                    foreach($data_i as $table_name => $all_i)
                    {
                        if(isset($all_i[0]))
                        {
                            foreach($all_i as $all_i_row)
                            {
                                $this->create_activity_log(array(
                                    'action'           => $this->action_name_added, 
                                    'action_table'     => $table_name, 
                                    'action_column'    => $all_i_row['primary']['column_name'], 
                                    'action_extras'    => $all_i_row['primary']['column_value']
                                ),$parent_id);
                            }
                        }
                        else
                        {
                            $this->create_activity_log(array(
                                'action'           => $this->action_name_added, 
                                'action_table'     => $table_name, 
                                'action_column'    => $all_i['primary']['column_name'], 
                                'action_extras'    => $all_i['primary']['column_value']
                            ),$parent_id);
                        }
                    }
                    
                    /* All your updations kept here */
                    foreach($data_u as $table_name => $all_u)
                    {
                        if(isset($all_u[0]))
                        {
                            foreach($all_u as $all_u_row)
                            {
                                foreach($all_u_row['db_changes'] as $field_name => $all_u_child_row)
                                {
                                    $this->create_activity_log(array(
                                        'action'                 => $this->action_name_changed, 
                                        'action_table'           => $table_name, 
                                        'action_column'          => $field_name, 
                                        'action_value'           => $all_u_child_row['new'], 
                                        'action_value_old'       => $all_u_child_row['old'],
                                        'action_extras'          => $all_u_row['primary']['column_value'],
                                    ),$parent_id);
                                }
                            }
                        }
                        elseif( isset($all_u['db_changes']))
                        {
                            foreach($all_u['db_changes'] as $field_name => $all_u_row)
                            {
                                $this->create_activity_log(array(
                                    'action'                 => $this->action_name_changed, 
                                    'action_table'           => $table_name, 
                                    'action_column'          => $field_name, 
                                    'action_value'           => $all_u_row['new'], 
                                    'action_value_old'       => $all_u_row['old'],
                                    'action_extras'          => $all_u['primary']['column_value'],
                                ),$parent_id);
                            }
                        }
                    }

                    /* All your deletion kept here */
                    foreach($data_d as $table_name => $all_d)
                    {
                        if(isset($all_d[0]))
                        {
                            foreach($all_d as $all_d_row)
                            {
                                $this->create_activity_log(array(
                                    'action'                 => $this->action_name_deleted, 
                                    'action_table'           => $table_name, 
                                    'action_column'          => $all_d_row['primary']['column_name'], 
                                    'action_extras'          => $all_d_row['primary']['column_value'],
                                    'action_value_old'       => $all_d_row['primary']['action_extras'],
                                ),$parent_id);
                            }
                        }
                        else
                        {
                            $this->create_activity_log(array(
                                'action'                 => $this->action_name_deleted, 
                                'action_table'           => $table_name, 
                                'action_column'          => $all_d['primary']['column_name'], 
                                'action_extras'          => $all_d['primary']['column_value'],
                                'action_value_old'       => $all_d['primary']['action_extras'],
                            ),$parent_id);
                        }
                    }
                }
                break;
            /* 
            case 'Custom': 
                // You can Clone the above case's code and customize for your self, 
                // and if you want to check the data in your hand just do this : 
                // echo '<pre>';print_r(compact('data_i','data_u','data_d'));echo '</pre>';
            */

            default: break; //I'm Just here for Nothing, ;)
        }
    }
}