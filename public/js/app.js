// Lightweight app JS (empty for now)
document.addEventListener('DOMContentLoaded', function () {
	// Subtle tilt for hero mockup
	var mock = document.getElementById('heroMockup')
	if (!mock) return

	var rect, cx, cy
	function updateCenter () {
		rect = mock.getBoundingClientRect()
		cx = rect.left + rect.width / 2
		cy = rect.top + rect.height / 2
	}
	updateCenter()
	window.addEventListener('resize', updateCenter)

	mock.addEventListener('mousemove', function (e) {
		var dx = (e.clientX - cx) / rect.width
		var dy = (e.clientY - cy) / rect.height
		var rotX = (+dy * 6).toFixed(2)
		var rotY = (-dx * 6).toFixed(2)
		mock.style.transform = 'perspective(900px) rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg)'
	})
	mock.addEventListener('mouseleave', function () {
		mock.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg)'
	})
});

// Reveal on scroll for elements with [data-reveal]
(function () {
	try {
		var items = document.querySelectorAll('[data-reveal]')
		if (!items.length || !('IntersectionObserver' in window)) return
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('reveal-in')
					io.unobserve(e.target)
				}
			})
		}, { threshold: 0.15 })
		items.forEach(function (el) { io.observe(el) })
	} catch (err) {
		// no-op; progressive enhancement
	}
})();


