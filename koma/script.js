$("#btn").click(() => {
  let raw = $("#text").val().split('\n')
  $("#text").val(raw.join(", "))
}) 

$("#btn").click(() => {
  let raw = $("#koma").val().split(',')
  $("#koma").val(raw.join("\n "))
}) 
