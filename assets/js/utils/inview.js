/**
 * opt_bud.inview closure that can apply the inview effect to all the specified elements
 * 
 * Once an element becomes "inview", the background will fade out and the element inview will appear to light up
 * The reverse will happen when the element is out of view
 */
opt_bud = window['opt_bud'] || {};
opt_bud.inview = (() => {
	/**
	 * Generates an array of fractional numbers 0 to num
	 * e.g. generateThreshold(5) = [0, 0.4, 0.6, 0.8, 1]
	 * e.g. generateThreshold(10) = [0, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
	 * 
	 * @param {int} num 
	 * @returns {array}
	 */
	const generateThreshold = num => {
		let threshold = [];
		const max = 1.0;

		// Generate the numbers
		for (let i = max; i <= num; i++) {
			const ratio = i / num;
			threshold.push(ratio);
		}

		// Add 0 to the beginning
		threshold.splice(0, 1, 0);

		return threshold;
	}

	/**
	 * The class that is applied to each element that are in view
	 */
	const style = 'inview';

	/**
	 * Observer options
	 */
	const options = { root: null, rootMargin: '0px', threshold: generateThreshold(20) };

	/**
	 * To prevent applying the inview effect to element initially in view
	 */
	let isFirstPass = true;	// Used so we can prevent elements from having the effect on DOM load

	/**
	 * Array of timers that are applied to all elements that are currently in view.
	 * This is used to remove the effect after 5 seconds
	 */
	let timers = [];

	/**
	 * Adds an observer to the list of elements
	 * 
	 * @param {NodeList} elements 
	 */
	const addObserverToElements = elements => {
		// Loop over all the elements and add them to the observer
		for (let i = 0; i < elements.length; i++) {
			const element = elements[i];

			// Add data id attribute so we can identify the element
			element.setAttribute('data-inview-id', i);

			// Add the observer to the element
			observer.observe(element);
		}
	}

	/**
	 * Removes the observer from the specified element
	 * 
	 * @param {DOMElement} element 
	 */
	const removeObserverForElement = element => {
		// Remove observer from element
		observer.unobserve(element);

		// Add classes to remove inview effect
		element.classList.remove(style);
		document.querySelector('body').classList.remove(style);
		element.classList.add('viewed');

		// Get the element id and clear the timeout
		let id = element.getAttribute('data-inview-id');
		clearTimeout(timers[id]);
		timers[id] = 0;
	}

	/**
	 * inview initializer method that will apply the inview effect to all the elements found by the specified identifier
	 * If maxElements is specified then the list may be reduced by the amount specified. 
	 * e.g. to only apply inview effect to 1 element: opt-bud.inview.init('.myclas', 1);
	 * 
	 * @param {string} identifier 
	 * @param {int} maxElements 
	 */
	const _init = (identifier, maxElements) => {
		let elements = document.querySelectorAll(identifier);
		maxElements = parseInt(maxElements);
		if (elements.length) {
			if (!isNaN(maxElements) && maxElements <= elements.length) {
				maxElements = Number(maxElements);
				elements = [].slice.call(elements, 0, maxElements);
			}

			addObserverToElements(elements);
		}
	}

	/**
	 * opt-bud.inview initializer that allows the user to specify the elements to apply the inview effect to
	 * 
	 * @param {array} elements 
	 */
	const _initWithElements = (elements) => {
		elements = elements || [];
		addObserverToElements(elements);
	}

	/**
	 * The intersection observer that lets us know when each element is within the viewport
	 */
	const observer = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			// Gets the id of the element that we use for the timeout
			const id = entry.target.getAttribute('data-inview-id');

			// Prevent entries from applying effect on
			if (isFirstPass && entry.isIntersecting && entry.intersectionRatio > 0) {
				//console.log('removing observer for id: ' + id);
				removeObserverForElement(entry.target);
				return;
			}

			// Remove after element has been shown for 5 seconds;
			if (entry.isIntersecting && timers[id] === undefined) {
				timers[id] = setTimeout(function () {
					removeObserverForElement(entry.target);
				}, 5000);
			}

			// If in view...
			if (entry.isIntersecting && !entry.target.classList.contains('viewed')) {

				// If this element is showing 95% or more...
				if (entry.intersectionRatio >= .95) {
					entry.target.classList.add(style);
					document.querySelector('body').classList.add(style);
				}

				// If this element is showing 94% or less and contains the inview style...
				if (entry.target.classList.contains(style) && entry.intersectionRatio <= .94) {
					entry.target.classList.remove(style);
					document.querySelector('body').classList.remove(style);

					// Prevent the effect from happening again
					removeObserverForElement(entry.target);
				}

			} else {
				//removeObserverForElement(entry.target);
			}

		});

		isFirstPass = false;
	}, options);

	/**
	 * Public API
	 */
	return {
		init: _init,
		initWithElements: _initWithElements
	}
})();