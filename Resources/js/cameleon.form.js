window.Cameleon = window.Cameleon || {};

Cameleon.form = {
	fixFormAction: function ($target, url) {
		var $form = $target.find('form');
		if (!$form.attr('action')) {
			$form.attr('action', url);
		}
	},

	uniqueId: {
		setup: function (id, options) {
			var lastValue;
			var $target = $(options.target);
			$('#' + id).keyup(function () {
				var value = $(this).val();
				if (lastValue === value) {
					return;
				}
				lastValue = value;
				var value = $(this).val();
				var sel = getInputSelection($(this).get(0));
				$(this).val(value = Cameleon.utils.slugize(value));
				setInputSelection($(this).get(0), sel.start, sel.end);

				$target.removeClass().html('...');
				$.ajax({
					url: options.url,
					data: {
						id: value
					},
					success: function (resp) {
						$target.addClass(resp.type)
						if (resp.errors) {
							var $errors = $('<ul/>');
							$.each(resp.errors, function (key, val) {
								$errors.append($('<li/>', {
									html: val
								}))
							});
							$target.html($errors);
						}
						$target.html(resp.result);
					}
				});
			});
		}
	},

	modalInit: function ($btn, $modal, options) {
		$modal.appendTo('body');
		var $target = $modal.find('.modal-data')
		$modal.on('hidden.bs.modal', function (e) {
			$target.html('');
		});
		$target.on('submit', 'form', function (e) {
			e.preventDefault();
			var $form = $(this),
				action = $form.attr('action');
			$.ajax({
				type: $form.attr('method'),
				url: action,
				data: $form.serialize(),
				success: function (resp, status, xhr) {
					if (options.submitCallback) {
						var r = options.submitCallback.call(this, resp, status, xhr);
						if (r === false) {
							return;
						}
					}
					$target.html(resp);
					$(document).trigger({
						type: 'cameleon-modal-loaded',
						$modal: $modal
					});
					Cameleon.form.fixFormAction($target, action);
				}
			});
		});
		$modal.find('[type=submit]').click(function (e) {
			$(this).closest('.modal').find('.modal-data form').submit();
		});
		options = $.extend({}, {
			callback: function (resp) {
				var u = options.url;
				if (options.data) {
					u += '?' + $.param(options.data);
				}
				Cameleon.form.fixFormAction($target, u);
			}
		}, options);
		Cameleon.modal.ajaxInit($btn, $modal, options);
	},

	summerNote: {
		setup: function (id, options) {
			function sendFile(file, editor, $editable) {
				var $modal;
				var data = new FormData();
				for (var i in options.formData) {
					data.append(i, options.formData[i]);
				}
				var fileFieldName = options.formData.form + '[' + options.formData.field + ']';
				data.append(fileFieldName, file);

				$modal = Cameleon.modal.launchModal({
					content: '<p>' + options.imageUploadLoadingTxt + '</p>'
				});
				$
				$modal.one('hidden.bs.modal', function () {
					$editable.focus();
				});
				$.ajax({
					data: data,
					type: "POST",
					contentType: 'multipart/form-data',
					url: options.imageUploadUrl,
					cache: false,
					contentType: false,
					processData: false,
					success: function (resp) {
						$modal.modal('hide');
						editor.restoreRange($editable);
						editor.insertImage($editable, resp.file.url);
					}
				});

				return false;
			}

			var options = $.extend({
				height: 300
			}, options);
			if (options.imageUploadUrl) {
				options.onImageUpload =
					function (files, editor, $editable) {
						sendFile(files[0], editor, $editable);
					};
			}
			$('#' + id).summernote(options);
		}
	},

	select2: {
		inputs: {},
		setup: function (id, options) {

			var options = $.extend({
				allowFreeEntries: true,
				multiple: true,
				width: 200
			}, options);

			if (options.allowFreeEntries) {
				options.tags = [];
				options.formatNoMatches = function () {
					return '';
				}
			}
			if (!options.multiple) {
				options.maximumSelectionSize = 1;
			}
			Cameleon.form.select2.inputs[id] = $('#' + id).select2(options);
		},
		appendChoice: function (id, item, multiple) {
			var $i = Cameleon.form.select2.inputs[id], data;
			if (multiple) {
				data = $i.select2('data');
				if (!data) {
					data = [];
				}
				data.push(item);
			} else {
				data = item;
			}
			$i.select2('data', data).val(item.id).trigger('change');
		}
	},

	dateRange: {
		setup: function (id) {
			$('#' + id + '_no_end_toggle').change(function () {
				var $i = $(this);
				var $t = $('#' + id + '-date-range-container .date-range-end :input');
				if ($i.is(':checked')) {
					$t.attr('disabled', true);
				} else {
					$t.removeAttr('disabled');
				}
			}).trigger('change');
		}
	},

	choices: {
		setupChoiceAdd: function (id, options) {
			var $modal = $('#' + id + '-modal');
			Cameleon.form.modalInit($('#' + id + '-add'), $modal, $.extend({
				submitCallback: function (resp, status, xhr) {
					var ct = xhr.getResponseHeader("content-type") || "";
					if (ct.indexOf('json') > -1) {
						$modal.modal('hide');
						options.appendChoice.call(this, id, resp, options.multiple);
						return false;
					}
				}
			}, options));
		}
	},

	gmapAutoComplete: {
		setup: function (field_id) {
			var formComponents = {
				street_number: 'short_name',
				route: 'long_name',
				locality: 'long_name',
				administrative_area_level_2: 'short_name',
				administrative_area_level_1: 'short_name',
				country: 'short_name',
				postal_code: 'short_name'
			}, input = document.getElementById(field_id + '_formatted_address'), autocomplete;

			function fill() {
				var place = autocomplete.getPlace(),
					$c = $('#' + field_id);
				$c.find(':input').val('');
				$c.find('#' + field_id + '_latitude').val(place.geometry.location.lat());
				$c.find('#' + field_id + '_longitude').val(place.geometry.location.lng());
				$c.find('#' + field_id + '_formatted_address').val(place.formatted_address);
				for (var i = 0; i < place.address_components.length; i++) {
					var addressType = place.address_components[i].types[0];
					if (formComponents[addressType]) {
						var val = place.address_components[i][formComponents[addressType]];
						$c.find('#' + field_id + '_' + addressType).val(val);
					}
				}
			}

			autocomplete = new google.maps.places.Autocomplete(input, {
				types: ['geocode']
			});
			google.maps.event.addListener(autocomplete, 'place_changed', function () {
				fill();
			});
		}
	},

	collection: {
		setup: function (subject) {
			$(subject).on('click', '.cameleon-collection-add', function (event) {
				event.preventDefault();
				var container = $(this).closest('[data-prototype]');
				var proto = container.attr('data-prototype');
				var protoName = container.attr('data-prototype-name') || '__name__';
				// Set field id
				var idRegexp = new RegExp(container.attr('id') + '_' + protoName, 'g');
				var count = (container.children().length - 1);
				proto = proto.replace(idRegexp, container.attr('id') + '_' + count);

				// Set field name
				var parts = container.attr('id').split('_');
				var nameRegexp = new RegExp(parts[parts.length - 1] + '\\]\\[' + protoName, 'g');
				proto = proto.replace(nameRegexp, parts[parts.length - 1] + '][' + count);
				$(proto)
					.insertBefore($(this).parent())
					.trigger('cameleon-admin-append-form-element')
				;
				$(document).trigger({
					type: 'cameleon-collection-item-added',
					idRegexp: idRegexp,
					count: count,
					containerId: container.attr('id')
				});
			});

			$(subject).on('click', '.cameleon-collection-delete', function (event) {
				event.preventDefault();
				$(document).trigger({type: 'cameleon-collection-item-pre-delete'});
				$(this).closest('.cameleon-collection-row').remove();
				$(document).trigger({type: 'cameleon-collection-item-post-delete'});
			});
		}
	}
};
