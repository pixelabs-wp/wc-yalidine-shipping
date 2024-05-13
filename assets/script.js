jQuery(document).ready(function ($) {
  // Variable to hold DataTable instance
  var credentialsTable;

  // Function to fetch data and initialize DataTable
  function fetchDataAndInitializeDataTable() {
    // Check if DataTable instance exists
    if ($.fn.DataTable.isDataTable('#credentials-table')) {
      // Destroy existing DataTable instance
      credentialsTable.destroy();
    }

    // Initialize DataTable
    credentialsTable = $('#credentials-table').DataTable({
      "processing": true,
      "ajax": {
        url: yalidineAjax.ajaxUrl,
        "type": "POST",
        "data": {
          "action": "get_yalidine_credentials"
        },
        "dataSrc": ""
      },
      "columns": [
        { "data": "stopdesk_name" },
        { "data": "api_id" },
        { "data": "token_id" },
        // Action column definition
        {
          "data": null,
          "defaultContent": "<button class='edit-btn'>Edit</button> <button class='delete-btn'>Delete</button>",
          "orderable": false,
          "searchable": false,
        }
      ],
      "columnDefs": [ // Define custom rendering for the action column
        {
          "targets": -1, // Target the last column (action column)
          "className": "dt-center" // Center align the content
        }
      ]
    });
  }

  // Initialize DataTable on document ready
  fetchDataAndInitializeDataTable();

  // Handle form submission
  $('#yalidine-form').submit(function (event) {
    event.preventDefault(); // Prevent default form submission
    var formData = $(this).serialize(); // Serialize form data

    // Send AJAX request to submit data
    jQuery.ajax({
      url: yalidineAjax.ajaxUrl,
      type: "POST",
      data: formData + '&action=save_yalidine_credentials', // Append action parameter
      success: function (response) {
        if (response.success) {
          alert("Credentials saved successfully!");
          // Fetch data and reinitialize DataTable upon success
          fetchDataAndInitializeDataTable();
          $('#edit-popup').hide();

          // Optionally clear the form or redirect to another page
        } else {
          alert("An error occurred: " + response.message);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX Error:", textStatus, errorThrown);
        alert("An error occurred while saving credentials.");
      }
    }); // End AJAX request
  });
  // Handle click event for delete buttons
  $('#credentials-table').on('click', '.delete-btn', function () {
    // Get the data of the row containing the clicked delete button
    var rowData = credentialsTable.row($(this).parents('tr')).data();

    // Confirm deletion
    if (confirm("Are you sure you want to delete this row?")) {
      // Send AJAX request to delete data from the database
      jQuery.ajax({
        url: yalidineAjax.ajaxUrl,
        type: "POST",
        data: {
          action: 'delete_yalidine_credentials', // Custom action name for deletion
          user_id: rowData.api_id, // Pass user ID to identify the row to delete
          // Add other necessary parameters for deletion, such as API ID, token ID, etc.
        },
        success: function (response) {
          if (response.success) {
            alert("Credentials deleted successfully!");
            // Refresh DataTable after deletion
            fetchDataAndInitializeDataTable();
            $('#edit-popup').hide();

          } else {
            alert("An error occurred: " + response.message);
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("AJAX Error:", textStatus, errorThrown);
          alert("An error occurred while deleting credentials.");
        }
      });
    }
  });





  // Handle click event for edit buttons
  $('#credentials-table').on('click', '.edit-btn', function () {
    // Get the data of the row containing the clicked edit button
    var rowData = credentialsTable.row($(this).parents('tr')).data();

    // Populate the form fields in the popup with the existing data
    $('#edit-stopdesk-name').val(rowData.stopdesk_name);
    $('#edit-api-id').val(rowData.api_id);
    $('#edit-token-id').val(rowData.token_id);

    // Show the popup
    $('#edit-popup').show();
  });



  // Handle form submission for editing
  $('#edit-form').submit(function (event) {
    event.preventDefault(); // Prevent default form submission
    var formData = $(this).serialize(); // Serialize form data

    console.log(formData);
    // Send AJAX request to update data
    jQuery.ajax({
      url: yalidineAjax.ajaxUrl,
      type: "POST",
      data: formData + '&action=edit_yalidine_credentials', // Append action parameter

      success: function (response) {
        if (response.success) {
          alert("Credentials updated successfully!");
          // Refresh DataTable after update
          fetchDataAndInitializeDataTable();
          // Hide the popup
          $('#edit-popup').hide();
        } else {
          alert("An error occurred: " + response.message);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX Error:", textStatus, errorThrown);
        alert("An error occurred while updating credentials.");
      }
    }); // End AJAX request
  });


  $('#delete_parcel').on('click', function (e) {
    console.log("HEre");

    e.preventDefault();
    var tracking = $(this).data('tracking');
    var orderID = $(this).data('id');

    console.log(orderID);

    jQuery.ajax({
      url: yalidineAjax.ajaxUrl,
      type: 'POST',
      data: {
        action: 'delete_parcel',
        tracking: tracking,
        orderID: orderID
      },
      success: function (response) {
        // Handle success response
        location.reload();
      },
      error: function (xhr, status, error) {
        // Handle error response
        console.error(xhr.responseText);
      }
    });
  });


  // Handle form submission
  $('#edit_shipping_details_submit').on('click', function (event) {
    event.preventDefault(); // Prevent default form submission

    // Get form data
    var formData = {
      tracking: $('#edit-tracking').val(),
      firstName: $('#edit-first-name').val(),
      lastName: $('#edit-last-name').val(),
      phone: $('#edit-phone').val(),
      address: $('#edit-address').val(),
      toWilayaName: $('#edit-to-walaya-name').val(),
      toCommuneName: $('#edit-to-commune-name').val()
      // Add more fields as needed
    };

    // Send AJAX request
    jQuery.ajax({
      url: yalidineAjax.ajaxUrl,
      type: 'POST',
      data: {
        action: 'edit_yalidine_parcels',
        formData: formData
      },
      success: function (response) {
        // Handle success response
        location.reload();
      },
      error: function (xhr, status, error) {
        // Handle error response
        console.error(xhr.responseText);
      }
    });
  });


});




jQuery(document).ready(function ($) {
  $('#parcel-details-table').DataTable({
    "order": [[10, 'desc']] // Sort by the first column (index 0) in descending order
  });
});

jQuery(document).ready(function ($) {
  // Handle click event of dashicons-visibility icon
  $('#parcel-details-table').on('click', '.view_parcel', function () {
    // Get the corresponding row data
    var rowData = $(this).closest('tr').find('td').map(function () {
      return $(this).text();
    }).get();

    // Construct the modal body content
    var modalContent = '<p><strong>Tracking:</strong> ' + rowData[0] + '</p>';
    modalContent += '<p><strong>Name:</strong> ' + rowData[1] + '</p>';
    modalContent += '<p><strong>Contact Phone:</strong> ' + rowData[2] + '</p>';

    modalContent += '<p><strong>Address:</strong> ' + rowData[3] + '</p>';

    modalContent += '<p><strong>From Wilaya:</strong> ' + rowData[4] + '</p>';

    modalContent += '<p><strong>To Wilaya:</strong> ' + rowData[5] + '</p>';

    modalContent += '<p><strong>Product List:</strong> ' + rowData[6] + '</p>';
    modalContent += '<p><strong>Price:</strong> ' + rowData[7] + '</p>';
    modalContent += '<p><strong>Last Status:</strong> ' + rowData[8] + '</p>';
    modalContent += '<p><strong>Date Last Status:</strong> ' + rowData[9] + '</p>';
    modalContent += '<p><strong>Current Center:</strong> ' + rowData[10] + '</p>';
    modalContent += '<p><strong>Payment Status:</strong> ' + rowData[11] + '</p>';

    // Add other fields as needed

    // Update the modal body with the content
    $('#shipment-details-modal .modal-body').html(modalContent);

    // Show the modal
    $('#shipment-details-modal').modal('show');
  });
});




jQuery(document).ready(function ($) {
  // Handle click event of dashicons-visibility icon
  $('#parcel-details-table').on('click', '.edit_parcel', function () {
    // Get the corresponding shipment details
    var rowData = $(this).closest('tr').find('td').map(function () {
      return $(this).text();
    }).get();

    var fullname = rowData[1];
    var names = fullname.split(" ");
    var firstName = names[0]; // First name
    var lastName = names.slice(1).join(" "); // Last name (join all parts after the first space)


    // Populate the form fields with the shipment details
    $('#edit-tracking').val(rowData[0]);
    $('#edit-first-name').val(firstName);
    $('#edit-last-name').val(lastName);

    $('#edit-phone').val(rowData[2]);

    $('#edit-address').val(rowData[3]);
    $('#edit-to-walaya-name').val(rowData[5]);
    $('#edit-to-commune-name').val(rowData[6]);
    
    // Show the modal
    $('#shipment-edit-details-modal').modal('show');
  });
});





