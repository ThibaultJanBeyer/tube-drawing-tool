var c_width = 450
var c_height = 450
var frameResize = false

$('#larger').click(function() {
	c_width = c_width + 50
	c_height = c_height + 50
	frameResize = true
	$('.width').val(c_width)
	$('.height').val(c_height)
})

$('#smaller').click(function() {
	c_width = c_width - 50
	c_height = c_height - 50
	frameResize = true
	$('.width').val(c_width)
	$('.height').val(c_height)
})

$('.submit').click(function() {
	c_width = parseInt($('.width').val())
	c_height = parseInt($('.height').val())
	//alert('Canvas size: ' + c_width + 'px * ' + c_height + 'px')
	frameResize = true
})

$('#ads').hide()
setTimeout(function(){
	$('#ads').show('slow')
}, 12000)

$('.hide-ads').click(function() {
	$('#ads').hide('slow')
})