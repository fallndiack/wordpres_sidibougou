(function( $ ) {

	// Add Color Picker to all inputs that have 'color-field' class
	$(
		function() {
			$( '.color-field' ).wpColorPicker();
		}
	);

	var rangeSlider = function(){
		var slider = $( '.range-slider' ),
		  range    = $( '.tint-color-opacity-slider__range' ),
		  value    = $( '.tint-color-opacity-slider__value' );

		slider.each(
			function(){

				value.each(
					function(){
						var value = $( this ).prev().attr( 'value' );
						$( this ).html( value );
					}
				);

				range.on(
					'input',
					function(){
						$( this ).next( value ).html( this.value );
					}
				);
			}
		);
	};

	rangeSlider();

})( jQuery );
