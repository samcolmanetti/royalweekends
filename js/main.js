
$("#generate_html").click(function(e) {
  var start_dt = $("#start_dt").val(); 
  var end_dt = $("#end_dt").val();
  
  $.ajax({
  url: "get_events.php",
  type: "get", //send it through get method
  data: { 
    start_date: start_dt, 
    end_date: end_dt 
  },
  success: function(response) {
    $("#html_output").val(response);
    $('#preview-box').html(response);
  },
  error: function(xhr) {
    //Do Something to handle error
    alert('ERROR');
  }
  }); 
});