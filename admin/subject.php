<?php

//grade.php

include('header.php');

?>

<div class="container" style="margin-top:30px">
  <div class="card">
  	<div class="card-header">
      <div class="row">
        <div class="col-md-9">Subject List</div>
        <div class="col-md-3" align="right">
          <button type="button" id="add_button" class="btn btn-info btn-sm">Add</button>
        </div>
      </div>
    </div>
  	<div class="card-body">
  		<div class="table-responsive">
        <span id="message_operation"></span>
        <table class="table table-striped table-bordered" id="subject_table">
          <thead>
            <tr>
              <th>Subject Name</th>
              <th>Branch Name</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>
  		</div>
  	</div>
  </div>
</div>

</body>
</html>

<div class="modal" id="formModal">
  <div class="modal-dialog">
    <form method="post" id="subject_form">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title" id="modal_title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">Subject Name <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="subject_name" id="subject_name" class="form-control" />
                <span id="error_subject_name" class="text-danger"></span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">Branch Name <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="branch_name" id="branch_name" class="form-control" />
                <span id="error_branch_name" class="text-danger"></span>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal footer -->
        <div class="modal-footer">
          <input type="hidden" name="subect_id" id="subect_id" />
          <input type="hidden" name="action" id="action" value="Add" />

          <input type="submit" name="button_action" id="button_action" class="btn btn-success btn-sm" value="Add" />
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal" id="deleteModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Delete Confirmation</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <h3 align="center">Are you sure you want to remove this?</h3>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" name="ok_button" id="ok_button" class="btn btn-primary btn-sm">OK</button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>



<script>
$(document).ready(function(){

  var dataTable = $('#subject_table').DataTable({
    "processing":true,
    "serverSide":true,
    "order":[],
    "ajax":{
      url:"subject_action.php",
      type:"POST",
      data:{action:'fetch'}
    },
    "columnDefs":[
      {
        "targets":[0, 1, 2],
        "orderable":false,
      },
    ],
  });

  $('#add_button').click(function(){
    $('#modal_title').text('Add Subject');
    $('#button_action').val('Add');
    $('#action').val('Add');
    $('#formModal').modal('show');
    clear_field();
  });

  function clear_field()
  {
    $('#subject_form')[0].reset();
    $('#error_subject_name').text('');
    $('#error_branch_name').text('');
  }

  $('#subject_form').on('submit', function(event){
    event.preventDefault();
    $.ajax({
      url:"subject_action.php",
      method:"POST",
      data:$(this).serialize(),
      dataType:"json",
      beforeSend:function()
      {
        $('#button_action').attr('disabled', 'disabled');
        $('#button_action').val('Validate...');
      },
      success:function(data)
      {
        $('#button_action').attr('disabled', false);
        $('#button_action').val($('#action').val());
        if(data.success)
        {
          $('#message_operation').html('<div class="alert alert-success">'+data.success+'</div>');
          clear_field();
          dataTable.ajax.reload();
          $('#formModal').modal('hide');
        }
        if(data.error)
        {
          if(data.error_subject_name != '')
          {
            $('#error_subject_name').text(data.error_subject_name);
          }
          if(data.error_branch_name != '')
          {
            $('#error_branch_name').text(data.error_branch_name);
          }
          else
          {
            $('#error_subject_name').text('');
            $('#error_branch_name').text('');
          }
        }
      }
    })
  });

  var subect_id = '';

  $(document).on('click', '.edit_subject', function(){
    subect_id = $(this).attr('id');
    clear_field();
    $.ajax({
      url:"subject_action.php",
      method:"POST",
      data:{action:'edit_fetch', subect_id:subect_id},
      dataType:"json",
      success:function(data)
      {
        $('#subject_name').val(data.subject_name);
        $('#subect_id').val(data.subect_id);
        $('#branch_name').val(data.branch_name);
        $('#modal_title').text('Edit Subject');
        $('#button_action').val('Edit');
        $('#action').val('Edit');
        $('#formModal').modal('show');
      }
    })
  });

  $(document).on('click', '.delete_subject', function(){
    subect_id = $(this).attr('id');
    $('#deleteModal').modal('show');
  });

  $('#ok_button').click(function(){
    $.ajax({
      url:"subject_action.php",
      method:"POST",
      data:{subect_id:subect_id, action:'delete'},
      success:function(data)
      {
        $('#message_operation').html('<div class="alert alert-success">'+data+'</div>');
        $('#deleteModal').modal('hide');
        dataTable.ajax.reload();
      }
    })
  });

});
</script>
