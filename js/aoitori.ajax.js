(function($, aoi, ajaxurl) {

	var AoiTori = {

		init: function() {
      this.checkTokensValid = aoi.check_tokens;
      if(this.checkTokensValid === '1')
        this.checkTokensAreValid();
      else
        $('#tokens_valid').text(aoi.no_check);
		},

		checkTokensAreValid: function() {
			$.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'check_tokens_are_valid',
          security: aoi.nonce
        },
        success: function(r) {
          if(r === 'true')
            $('#tokens_valid').html($('<span/>').css('color', '#228800').text(aoi.valid_tokens));
          else
            $('#tokens_valid').html($('<span/>').css('color', '#882200').text(aoi.invalid_tokens));
        }
      })
		}
	}

	AoiTori.init();

})(jQuery, AoiToriAjax, ajaxurl);
