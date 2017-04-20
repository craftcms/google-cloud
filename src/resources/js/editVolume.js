var $googleProjectId = $('.google-project-id'),
	$googleKeyFileContents = $('.google-key-file-contents'),
	$googleBucketSelect = $('.google-bucket-select > select'),
	$googleRefreshBucketsBtn = $('.google-refresh-buckets'),
	$googleRefreshBucketsSpinner = $googleRefreshBucketsBtn.parent().next().children(),
	refreshingGoogleBuckets = false;

$googleRefreshBucketsBtn.click(function()
{
	if ($googleRefreshBucketsBtn.hasClass('disabled'))
	{
		return;
	}

	$googleRefreshBucketsBtn.addClass('disabled');
	$googleRefreshBucketsSpinner.removeClass('hidden');

	var data = {
		projectId:  $googleProjectId.val(),
		keyFileContents: $googleKeyFileContents.val()
	};

	Craft.postActionRequest('google-cloud', data, function(response, textStatus)
	{
		$googleRefreshBucketsBtn.removeClass('disabled');
		$googleRefreshBucketsSpinner.addClass('hidden');

		if (textStatus == 'success')
		{
			if (response.error)
			{
				alert(response.error);
			}
			else if (response.length > 0)
			{
				var currentBucket = $googleBucketSelect.val(),
					currentBucketStillExists = false;

				refreshingGoogleBuckets = true;

				$googleBucketSelect.prop('readonly', false).empty();

				for (var i = 0; i < response.length; i++)
				{
					if (response[i].bucket == currentBucket)
					{
						currentBucketStillExists = true;
					}

					$googleBucketSelect.append('<option value="'+response[i].bucket+'" data-url-prefix="'+response[i].urlPrefix+'">'+response[i].bucket+'</option>');
				}

				if (currentBucketStillExists)
				{
					$googleBucketSelect.val(currentBucket);
				}

				refreshingGoogleBuckets = false;

				if (!currentBucketStillExists)
				{
					$googleBucketSelect.trigger('change');
				}
			}
		}
	});
});

$googleBucketSelect.change(function()
{
	if (refreshingGoogleBuckets)
	{
		return;
	}

	var $selectedOption = $googleBucketSelect.children('option:selected');

	$('.volume-url').val($selectedOption.data('url-prefix'));
});