/**
 * The multi-select utilizes slide-toggle
 */
opt_bud = window['opt_bud'] || {};
opt_bud.multi_select = (($) => {
	// List of all the selected items
	let _selected_items = [];

	// This will contain the default tag container text
	let _default_text = '';

	/**
	 * Filters the list by the specified value
	 */
	const filterFunction = () => {
		//let input, filter, ul, li;
		const $input = $('.search-box');
		const filter = $input.val().toUpperCase();

		$('.multi-select ul li').each((index, element) => {
			const value = element.textContent || element.innerText;
			if (value.toUpperCase().indexOf(filter) > -1) {
				element.style.display = "";
			} else {
				element.style.display = "none";
			}
		});
	}

	/**
	 * Finds the associated li element containing the matched specified value and removes the selected class
	 * 
	 * @param {string} value 
	 * @returns 
	 */
	const updateSelectedItemByValue = value => {
		$('.multi-select ul li').each((index, element) => {
			if (element.textContent === value) {
				element.classList.remove('selected');
				return;
			}
		});
	}

	/**
	 * Hides the selected item from the list and adds the tag to the container
	 * 
	 * @param {DOMElement} element 
	 */
	const selectItem = element => {
		let value = element.textContent;
		let index = _selected_items.indexOf(value);

		if (index > -1) {
			// Remove item if already selected
			/*
			_selected_items.splice(index, 1);
			element.classList.remove('selected');
			*/
		} else {
			// Add item to the tag container
			_selected_items.push(value);
			// Add selected class to hide item from the list
			element.classList.add('selected');
		}
		updateTagContainer();
	}

	/**
	 * Refreshes the tag container by clearing it out and re-adding the tags
	 */
	const updateTagContainer = () => {
		let $tagContainer = $('.multi-select .tag-container');

		// Set the default tag container text before adding any tags
		if (_default_text === '') {
			_default_text = $tagContainer.html();
		}

		// Clear existing tags
		$tagContainer.html('');

		_selected_items.forEach(item => {
			let $tag = $(`<span class="tag">${item}</span>`);
			$tag.on('click', function (e) {
				e.stopPropagation();
				removeItem(item);
			});
			$tagContainer.append($tag);
		});

		// Set the text back to the default if not tags are present
		if ($tagContainer.html() === '') {
			$tagContainer.html(_default_text);
		}
	}

	/**
	 * Removes the tag and shows the item back in the list
	 * 
	 * @param {string} item 
	 */
	const removeItem = item => {
		let index = _selected_items.indexOf(item);
		if (index > -1) {
			// Removes the selected tag from the associated removed item
			updateSelectedItemByValue(item);
			// Remove item
			_selected_items.splice(index, 1);
		}
		updateTagContainer();
	}

	/**
	 * 
	 */
	$(document).on('input', '.search-box', function (e) {
		filterFunction(e);
	});

	/**
	 * 
	 */
	$(document).on('click', '.dropdown-content li', function (e) {
		selectItem(this);
	});
})(jQuery);