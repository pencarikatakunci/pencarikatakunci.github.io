$("#btnbarisbaru").click(() => {
  let raw = $("#barisbaru").val().split('\n')
  $("#barisbaru").val(raw.join(", "))
}) 

$("#btnkoma").click(() => {
  let raw = $("#koma").val().split(',')
  $("#koma").val(raw.join("\n "))
}) 
