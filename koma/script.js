
$("#btn").click(() => {
  let raw = $("#line").val().split('\n')
  $("#line").val(raw.join(", "))
}) 

$("#btn").click(() => {
  let raw = $("#koma").val().split(',')
  $("#koma").val(raw.join("\n "))
}) 
