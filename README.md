# Custom Audit Trail In Code Igniter
here's log keeping model and its usage shown below

# A one more new model ? why ?
Recently i was in need to have a model which can help me to keep track of each database change with allowing me to go through my conditionds as well. After a lots of try and i didn't find anything for the same, then i decided to start writing my own. 

as i created log file and simultaniously integrated into my project, and as i proceed further it got much clearer, i have to add something, drop something as well, and finally i got every thing in one log file. i tried to generalize as i could. but feel free to change it as you like. 

---

# Basic Usage : 

First of all, include in your file **$this->load->model('ActivityLogModel');** obviously, or you can put it into autoload (**config/autoload.php**)

consider following structure in example of User CRUD

first you will goun to have 3 arrays:

	$all_insertions = array();
	$all_updations = array();
	$all_deletions = array();

then you can fill above arrays using the respective functions of model as follows : 

#### Case Of Insertion
	/*
		after all insertion,
		put the inserted user_id in any variable,let say it's $user_id
	*/

	/* Preparing the log variable */ 
	$all_insertions['users'][] = $this->db->where('user_id',$user_id)->get('users')->row_array();
	
	/* Saving the log variable */ 
	$all_insertions = $this->ActivityLogModel->insertion_logs(
		$all_insertions, 	/* Main Insertion Array */
		'users',			/* Name Of Table */
		'user_id' 			/* Name Of Primary Column */
	);

#### Case Of Updation
	/*
		here we assuming 
		we already have user_id in any variable,let say $user_id,
		and we will put same 2 lines around our updation code, 
		one before and one after
	*/

	/* Preparing the log variable */ 
	$all_updations['users'][] = $this->db->where('user_id',$user_id)->get('users')->row_array();

		/* Note : Your Updation Code Goes Here */

	/* Preparing the log variable */ 
	$all_updations['users'][] = $this->db->where('user_id',$user_id)->get('users')->row_array();

	/* Saving the log variable */ 
	$all_updations = $this->ActivityLogModel->updation_logs(
		$all_updations, /* Main Updation Array */
		'users', 		/* Name Of Table */
		'user_id' 		/* Name Of Primary Column */
	);


#### Case Of Deletion

	/*
		here we assuming 
		we already have user_id in any variable,let say $user_id,
		and we will put 1 lines before our deletion code
	*/

	/* Preparing the log variable */ 
	$all_deletions['users'][] = $this->db->where('user_id',$user_id)->get('users')->row_array();

		/* Note : Your Deletion Code Goes Here */
	
	/* Saving the log variable */ 
	$all_deletions = $this->ActivityLogModel->deletion_logs(
		$all_deletions, 	/* Main Deletion Array */
		'users',			/* Name Of Table */
		'user_id' 			/* Name Of Primary Column */
	);

### Now Final Step, Process the prepared arrays

    $this->ActivityLogModel->save_activity_logs(
        1, 					/* It Just a type, leave it 1 for Now */
        $all_insertions,	
        $all_updations,
        $all_deletions,
        $parent_id 			/* Optional, Parent Value if you want to asociate with it. */
    );

* Note  : If you want to customize, and you have create any other case in model file, then pass it here in first parameter in save_activity_logs function call


----

# Advance Usage : 

* If you have multiple insertions, updations or deletions, 
	then you can attach unique index with it as follows ,
	lets assume we are keeping index in variable $insertion_index, $updation_index and $deletion_index respectively


	
#### Case Of Insertion

	/* Preparing the log variable */ 
	$all_insertions['users'][$insertion_index][] = $this->db->where('user_id',$user_id)->get('users')->row_array();
	
	/* Saving the log variable */ 
	$all_insertions = $this->ActivityLogModel->insertion_logs(
		$all_insertions, 	/* Main Insertion Array */
		'users',			/* Name Of Table */
		'user_id', 			/* Name Of Primary Column */
		$insertion_index 	/* Unique Index */
	);

#### Case Of Updation 

	/* Preparing the log variable */ 
	$all_updations['users'][$updation_index][] = $this->db->where('user_id',$user_id)->get('users')->row_array();

		/* Note : Your Updation Code Goes Here */

	/* Preparing the log variable */ 
	$all_updations['users'][$updation_index][] = $this->db->where('user_id',$user_id)->get('users')->row_array();

	/* Saving the log variable */ 
	$all_updations = $this->ActivityLogModel->updation_logs(
		$all_updations, /* Main Updation Array */
		'users', 		/* Name Of Table */
		'user_id', 		/* Name Of Primary Column */
		$updation_index /* Unique Index */
	);

#### Case Of Deletion

	/* Preparing the log variable */ 
	$all_deletions['users'][$deletion_index][] = $this->db->where('user_id',$user_id)->get('users')->row_array();

		/* Note : Your Deletion Code Goes Here */
	
	/* Saving the log variable */ 
	$all_deletions = $this->ActivityLogModel->deletion_logs(
		$all_deletions, 	/* Main Deletion Array */
		'users',			/* Name Of Table */
		'user_id', 			/* Name Of Primary Column */
		$deletion_index		/* Unique Index */
	);

#### NOTE :
*   If you want to Save any record which is goind to be deleted,
	then you can specify column name of which you want to keep value of,
	in 5th parameter in deletion_logs() function call say 'user_name' 

----

### Okey, That's all for right now,
## Enjoy! Happy Coding ;)
