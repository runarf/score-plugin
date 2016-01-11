jQuery(document).ready(function($) {
  var num = $("#goalscorerTable tr").length - 2;
  $("#goal_add").click(function(event){
    event.preventDefault();
    console.log("Number is " + num);
    console.log("Appending table");
    num++;
    var goalNum = 'goal[' + num + ']';
    $("#goalscorerTable").append(
      '<tr><td>' + num + '</td>' +
      '<td> <input type="text" size="3" name="' + goalNum +'[min]"></input></td>' +
      '<td> <input type="text" size="10" name="' + goalNum + '[name]"></input></td>' +
      '<td> <input type="checkbox" name="' + goalNum +'[home]"></input></td></tr>'
    );
    $("#numGoals").val(num);
  });

  $("#goal_delete").click(function(event) {
    event.preventDefault();
    if ($("#goalscorerTable tr").length > 2) {
      $("#goalscorerTable tr").last().remove();
      num--;
      $("#numGoals").val(num);
    }
  })
});
