( function () {

	var RepeaterView, ControlModel;

	ControlModel = Backbone.Model.extend( {
		defaults : function () {
			return {
				type       : 'text',
				value      : '',
				input_id   : '',
				input_name : '',
				repeatable : true
			};
		}
	} );

	RepeaterView = Backbone.View.extend( {
		el : '.fields-control', events : {
			'click .fields-repeatable-control-add-new' : 'addControl',
			'click .fields-repeatable-control-remove'  : 'removeControl'
		}, addControl                           : function ( event ) {

			var newControl, ControlView, data = {
				type       : this.$el.data( 'fields-type' ),
				value      : '',
				input_id   : this.$el.attr( 'id' ),
				input_name : this.$el.data( 'fields-input-name' ),
				repeatable : true
			};

			event.preventDefault();

			ControlView = Backbone.View.extend( {
				templateID : 'fields-control-' + data.type + '-content',
				template   : function ( data ) {
					return wp.template( this.templateID )( data );
				},
				render     : function () {
					return this.template( this.model.toJSON() );
				}
			} );

			newControl = new ControlView( {
				model : new ControlModel
			} );

			newControl.model.set( data );

			jQuery( event.srcElement ).before( '<div class="fields-repeatable-input">' + (newControl.render()) + '</div>' );

		}, removeControl                        : function ( event ) {

			jQuery( event.srcElement ).closest( '.fields-repeatable-input' ).remove();

		}
										 } );

	new RepeaterView;

} ).call( this );