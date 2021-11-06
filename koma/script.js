$("#line").click(() => {
  let raw = $("#line").val().split('\n')
  $("#line").val(raw.join(", "))
}) 

$("#koma").click(() => {
  let raw = $("#koma").val().split(',')
  $("#koma").val(raw.join("\n "))
}) 
