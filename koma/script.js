$("#btn").click(() => {
  let raw = $("#text").val().split('\n')
  $("#text").val(raw.join(", "))
}) 
