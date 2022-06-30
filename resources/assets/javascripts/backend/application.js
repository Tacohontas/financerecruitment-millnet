/* global wp */
/* global financerecruitment_millnet_vars */
jQuery($ => {

	const mergeObjects = (object1, object2) => {
		for (const key in object2) {
			if (object1.hasOwnProperty(key)) {
				if (typeof object2[key] === 'object' && object2[key] !== null) {
					object1[key] = mergeObjects(object1[key], object2[key]);
				} else {
					object1[key] = object2[key];
				}
			}
		}

		return object1;
	};

	const initWpEditors = () => {
		$('.financerecruitment-millnet-input-admin-editor.init').each((index, el) => {
			$(el).removeClass('init');
			const editorId = $(el).attr('id');

			// setup default settings, same as in G-Theme
			let settings = {
				quicktags: true,
				mediaButtons: true,
				tinymce: {
					toolbar1:
						'formatselect,styleselect,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,undo,redo',
					style_formats: financerecruitment_millnet_vars.style_formats, // eslint-disable-line camelcase
					content_css: financerecruitment_millnet_vars.editor_styles, // eslint-disable-line camelcase
					height: 300,
				},
			};

			if ($(el).data('settings')) {
				const dataSettings = JSON.parse(decodeURIComponent($(el).data('settings')));
				settings = mergeObjects(settings, dataSettings);
			}

			wp.editor.initialize(editorId, settings);
		});
	};

	initWpEditors();

	const removeEditors = ($editors) => {
		$editors.each((index, el) => {
			const editorId = $(el).attr('id');
			wp.editor.remove(editorId);
			$(el).addClass('init');
		});
	};

	/**
	 * Class for Repeatable Fields in Admin
	 */
	class RepeatableField {
		/**
		 * Repeatable Field Class Constructor
		 *
		 * @param {HTMLElement} $field
		 */
		constructor($field) {
			this.table = $field;
			this.tableBody = $field.find(' > tbody');
			this.rowTemplate = $field.parent().find('.financerecruitment-millnet-input-admin-repeatable-template').html();
			this.addRowClass = '.financerecruitment-millnet-input-admin-repeatable-add';
			this.removeRowClass = '.financerecruitment-millnet-input-admin-repeatable-remove';
			this.moveUpClass = '.financerecruitment-millnet-input-admin-repeatable-move-up';
			this.moveDownClass = '.financerecruitment-millnet-input-admin-repeatable-move-down';
			this.maxIndex = this.tableBody.find('tr').length;

			this.init();
		}

		/**
		 * Initiating function for class
		 */
		init() {
			this.addEventListeners();
		}

		/**
		 * Adding event listeners to repeatable fields
		 */
		addEventListeners() {
			this.table.on('click', this.addRowClass, () => {
				this.appendRow();
			});

			this.table.on('click', this.removeRowClass, (e) => {
				$(e.target).closest('tr').remove();

				if (!this.tableBody.find('tr').length) {
					this.appendRow();
				}
			});

			this.table.on('click', this.moveUpClass, (e) => {
				const $parentRow = $(e.target).closest('tr');
				const $prevRow = $parentRow.prev('tr');
				const $repeater = $parentRow.closest('table');

				if ($prevRow.length) {
					removeEditors($parentRow.find('.financerecruitment-millnet-input-admin-editor'));
					$parentRow.insertBefore($prevRow);
					initWpEditors();
					this.reSetRowIndexes($repeater);
				}
			});

			this.table.on('click', this.moveDownClass, (e) => {
				const $parentRow = $(e.target).closest('tr');
				const $nextRow = $parentRow.next('tr');
				const $repeater = $parentRow.closest('table');

				if ($nextRow.length) {
					removeEditors($parentRow.find('.financerecruitment-millnet-input-admin-editor'));
					$parentRow.insertAfter($nextRow);
					initWpEditors();
					this.reSetRowIndexes($repeater);
				}
			});
		}

		/**
		 * Adjust the indexes on the input fields to get correct order when saving
		 *
		 * @param {$} $repeater Repeatable table
		 * @return {undefined}
		 */
		reSetRowIndexes($repeater) {
			const $rows = $repeater.find('tbody tr');
			if ($rows.length) {
				$rows.each((index, elm) => {
					const $elm = $(elm);
					const regex = /(.+?\[)\d(\].+?)/g;

					// Adjust the inputs
					const $inputs = $elm.find('[name]');
					$inputs.each((_index, input) => {
						const $input = $(input);
						const name = $input.attr('name');
						const id = $input.attr('id');

						$input.attr('name', name.replace(regex, '$1' + index + '$2'));
						$input.attr('id', id.replace(regex, '$1' + index + '$2'));
					});

					// Adjust the labels
					const $labels = $elm.find('[for]');
					$labels.each((_index, label) => {
						const $label = $(label);
						const forAttr = $label.attr('for');

						$label.attr('for', forAttr.replace(regex, '$1' + index + '$2'));
					});
				});
			}
		}

		/**
		 * Appends a row in repeatable field
		 */
		appendRow() {
			const rowTemplate = this.rowTemplate.replace(/({{index}})/g, this.maxIndex);
			this.maxIndex++;
			this.tableBody.append(rowTemplate);

			initWpEditors();
		}
	}

	$('.financerecruitment-millnet-input-admin-repeatable').each((_, el)  => {
		new RepeatableField($(el));
	});

	/**
	 * Class for Media Field in admin
	 */
	class MediaField {
		/**
		 * Constructor for Media Field Class
		 */
		constructor() {
			this.wrapperClass = '.financerecruitment-millnet-input-admin-media-wrapper';
			this.addButtonClass = '.financerecruitment-millnet-input-admin-media-add';
			this.removeButtonClass = '.financerecruitment-millnet-input-admin-media-remove';
			this.inputClass = '.financerecruitment-millnet-input-admin-media';
			this.previewClass = '.financerecruitment-millnet-input-admin-media-preview';
			this.mediaUploader = null;

			this.addEventListeners();
		}

		/**
		 * Adds event listeners for Media Fields
		 */
		addEventListeners() {
			$('body').on('click', this.addButtonClass, (e) => {
				const $field = $(e.target).closest(this.wrapperClass);
				let $previews = $field.find(this.previewClass);
				const allowMultiple = $field.data('multiple');
				const mimeTypes = $field.data('mime-types');
				
				if (this.mediaUploader) {
					this.mediaUploader.open();
					return;
				}

				this.mediaUploader = wp.media.frames.file_frame = wp.media({ // eslint-disable-line camelcase
					title: financerecruitment_millnet_vars.CHOOSE_MEDIA,
					button: {
						text: financerecruitment_millnet_vars.CHOOSE_MEDIA,
					},
					multiple: allowMultiple,
					library: {
						type: mimeTypes.split(',')
					},
				});

				this.mediaUploader.on('select', () => {
					const attachment = this.mediaUploader.state().get('selection').toJSON();

					for (let i = 0; i < attachment.length; i++) {
						const $previewClone =
							$previews.first().find('input').val() && allowMultiple
								? $previews.first().clone()
								: $previews.first();

						if ('mime' in attachment[i]) {
							if (attachment[i].mime.match(/image/)) {
								$previewClone.find('img').attr('src', attachment[i].url);
								$previewClone.removeClass('is-icon');
							} else if ('icon' in attachment[i]) {
								$previewClone.find('img').attr('src', attachment[i].icon);
								$previewClone.addClass('is-icon');
							}
						}

						let fileName = '';

						if ('filename' in attachment[i]) {
							fileName = attachment[i].filename;
						}

						$previewClone.find('.financerecruitment-millnet-input-admin-media-file-name').text(fileName);

						$previewClone.find('input').val(attachment[i].id);

						if (parseInt($previews.first().find('input').val())) {
							$previews.parent().append($previewClone);
						} else {
							$previews.parent().html('').append($previewClone);
						}

						$previews = $field.find(this.previewClass);
					}

					$previews.addClass('has-file');
					this.mediaUploader = false;
				});

				this.mediaUploader.open();
			});

			$('body').on('click', this.removeButtonClass, (e) => {
				const $field = $(e.target).closest(this.wrapperClass);
				const $preview = $(e.target).closest(this.previewClass);
				const $input = $preview.find(this.inputClass);

				if ($field.find(this.previewClass).length > 1) {
					$preview.remove();
				} else {
					$preview.find('.financerecruitment-millnet-input-admin-media-file-name').text('');
					$preview.removeClass('has-file');
					$input.val('');
				}
			});
		}
	};

	if ($('.financerecruitment-millnet-input-admin-media-wrapper').length) {
		new MediaField();
	}

	/**
	 * Class for Conditional Fields
	 */
	class ConditionalFields {
		/**
		 * Constructor for conditional fields class
		 *
		 * @param {HTMLElement} $fields
		 */
		constructor($fields) {
			this.conditionalFields = $fields;
			this.init();
		}

		/**
		 * Initiating method
		 */
		init() {
			this.addEventListeners();
		}

		/**
		 * Adds event listener to conditional fields
		 */
		addEventListeners() {
			this.conditionalFields.each((_index, el) => {
				const $field = $(el);

				const fieldName = $(el).data('cfield');
				const checkValue = $(el).data('cvalue');
				const operator = $(el).data('operator');
				const $cField = $('[name="' + fieldName + '"]');

				// check conditional fields on page load
				this.checkConditional($field, $cField, checkValue, operator);

				// check conditional fields on change
				$('body').on('change', '[name="' + fieldName + '"]', (_event) => { // eslint-disable-line no-unused-vars
					this.checkConditional($field, $cField, checkValue, operator);
				});
			});
		}

		/**
		 * Check for conditional fields
		 *
		 * @param {$} $field Dependent field
		 * @param {$} $cField Field to check against
		 * @param {mixed} checkValue Value to be compared
		 * @param {string} operator Operator
		 *
		 * @return {undefined}
		 */
		checkConditional($field, $cField, checkValue, operator) {
			if ($cField.length === 0) {
				console.error(financerecruitment_millnet_vars.i18n.field_not_found);
				return;
			}

			let currentValue = '';
			if (typeof operator === 'undefined') {
				operator = '=';
			}

			if ($cField.length > 1) {
				currentValue = [];

				$cField.each((_index, elm) => {
					const $elm = $(elm);

					if ($elm.is(':checked')) {
						currentValue.push($elm.val());
					}
				});
			} else {
				currentValue = $cField.val();
			}

			let fieldType = 'string';

			if (typeof currentValue !== 'string') {
				// Let's make an array comparison
				fieldType = 'array';
			}

			let show = false;
			switch (fieldType) {
			case 'string':
				switch (operator) {
				case 'contains':
					show = currentValue.indexOf(checkValue) !== -1;
					break;
				case 'not contains':
					show = currentValue.indexOf(checkValue) === -1;
					break;
				case '=':
				case '==':
					show = currentValue.toString().toLowerCase() === checkValue.toString().toLowerCase();
					break;
				case '===':
					show = currentValue.toString() === checkValue.toString();
					break;
				case '!=':
					show = currentValue.toString().toLowerCase() !== checkValue.toString().toLowerCase();
					break;
				case '!==':
					show = currentValue.toString() !== checkValue.toString();
					break;
				}
				break;
			case 'array':
				switch (operator) {
				case 'contains':
					show = currentValue.indexOf(checkValue) !== -1;
					break;
				case 'not contains':
					show = currentValue.indexOf(checkValue) === -1;
					break;
				case '=':
				case '==':
					show =
						currentValue.length === 1 &&
						currentValue[0].toString().toLowerCase() === checkValue.toString().toLowerCase();
					break;
				case '===':
					show = currentValue.length === 1 && currentValue[0] === checkValue;
					break;
				case '!=':
					show =
						currentValue.length !== 1 ||
						currentValue[0].toString().toLowerCase() !== checkValue.toString().toLowerCase();
					break;
				case '!==':
					show = currentValue.length !== 1 || currentValue[0] !== checkValue;
					break;
				}
				break;
			}

			if (show) {
				$field.closest('tr').removeClass('hidden');
			} else {
				$field.closest('tr').addClass('hidden');
			}
		}
	};

	if ($('[data-cfield]').length) {
		new Conditional_Fields($('[data-cfield]'));
	}
});
