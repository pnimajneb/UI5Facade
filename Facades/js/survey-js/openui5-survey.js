/**
 *
 * This script disables UI5's touch event interceptor.
 * When that feature is enabled the touch event in mobile devices are not delivered
 * to surveyJS elements and that makes the boolean switches unusable. We are expliciptly 
 * disabling UI5's interceptor event listeners (touchstart and touchend), which are
 * registered to document element.
 * 
 * To check details see jquery-mobile-custom-dbg.js 
 * 
 */
(function() {
	// Check if userAgent is a mobile device or not
	const toMatch = [
        /Android/i,
        /webOS/i,
        /iPhone/i,
        /iPad/i,
        /iPod/i,
        /BlackBerry/i
    ];
    
    const isMobile = toMatch.some((toMatchItem) => {
        return navigator.userAgent.match(toMatchItem);
    });

	// If not mobile userAgent then no need to disable touch event listeners
	if (!isMobile) return; 

	// Remove touchstart and touchend interceptors
	setTimeout(() => {
		$(document).off('touchstart');
		$(document).off('touchend');
	}, 1000);
}())