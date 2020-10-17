<?php

//grade_action.php

include('database_connection.php');

session_start();

$output = '';

if(isset($_POST["action"]))
{
	if($_POST["action"] == "fetch")
	{
		$query = "SELECT * FROM tbl_lectures ";
		if(isset($_POST["search"]["value"]))
		{
			$query .= 'WHERE lecture_name LIKE "%'.$_POST["search"]["value"].'%" ';
		}
		if(isset($_POST["order"]))
		{
			$query .= 'ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$query .= 'ORDER BY lecture_id DESC ';
		}
		if($_POST["length"] != -1)
		{
			$query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$statement = $connect->prepare($query);
		$statement->execute();
		$result = $statement->fetchAll();
		$data = array();
		$filtered_rows = $statement->rowCount();
		foreach($result as $row)
		{
			$sub_array = array();
			$sub_array[] = $row["lecture_name"];
			$sub_array[] = '<button type="button" name="edit_lecture" class="btn btn-primary btn-sm edit_lecture" id="'.$row["lecture_id"].'">Edit</button>';
			$sub_array[] = '<button type="button" name="delete_lecture " class="btn btn-danger btn-sm delete_lecture " id="'.$row["lecture_id"].'">Delete</button>';
			$data[] = $sub_array;
		}

		$output = array(
			"draw"			=>	intval($_POST["draw"]),
			"recordsTotal"		=> 	$filtered_rows,
			"recordsFiltered"	=>	get_total_records($connect, 'tbl_lectures'),
			"data"				=>	$data
		);

		
	}
	if($_POST["action"] == 'Add' || $_POST["action"] == "Edit")
	{
		$lecture_name = '';
		$error_lecture_name = '';
		$error = 0;
		if(empty($_POST["lecture_name"]))
		{
			$error_lecture_name = 'Grade Name is required';
			$error++;
		}
		else
		{
			$lecture_name = $_POST["lecture_name"];
		}
		if($error > 0)
		{
			$output = array(
				'error'							=>	true,
				'error_lecture_name'				=>	$error_lecture_name
			);
		}
		else
		{
			if($_POST["action"] == "Add")
			{
				$data = array(
					':lecture_name'				=>	$lecture_name
				);
				$query = "
				INSERT INTO tbl_lectures 
				(lecture_name) 
				SELECT * FROM (SELECT :lecture_name) as temp 
				WHERE NOT EXISTS (
					SELECT lecture_name FROM tbl_lectures WHERE lecture_name = :lecture_name
				) LIMIT 1
				";
				$statement = $connect->prepare($query);
				if($statement->execute($data))
				{
					if($statement->rowCount() > 0)
					{
						$output = array(
							'success'		=>	'Data Added Successfully',
						);
					}
					else
					{
						$output = array(
							'error'					=>	true,
							'error_lecture_name'		=>	'Lecture Name Already Exists'
						);
					}
				}
			}
			if($_POST["action"] == "Edit")
			{
				$data = array(
					':lecture_name'			=>	$lecture_name,
					':lecture_id'				=>	$_POST["lecture_id"]
				);

				$query = "
				UPDATE tbl_lectures 
				SET lecture_name = :lecture_name 
				WHERE lecture_id = :lecture_id
				";
				$statement = $connect->prepare($query);
				if($statement->execute($data))
				{
					$output = array(
						'success'		=>	'Data Updated Successfully',
					);
				}
			}
		}
	}

	if($_POST["action"] == "edit_fetch")
	{
		$query = "
		SELECT * FROM tbl_lectures WHERE lecture_id = '".$_POST["lecture_id"]."'
		";
		$statement = $connect->prepare($query);
		if($statement->execute())
		{
			$result = $statement->fetchAll();
			foreach($result as $row)
			{
				$output["lecture_name"] = $row["lecture_name"];
				$output["lecture_id"] = $row["lecture_id"];
			}
		}
	}

	if($_POST["action"] == "delete")
	{
		$query = "
		DELETE FROM tbl_lectures 
		WHERE lecture_id = '".$_POST["lecture_id"]."'
		";
		$statement = $connect->prepare($query);
		if($statement->execute())
		{
			echo 'Data Deleted Successfully';
		}
	}

	echo json_encode($output);
}

?>