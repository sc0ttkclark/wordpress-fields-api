ControlModel = Backbone.Model.extend
	defaults: ->
		type:       'text',
		value:      '',
		input_id:   '',
		input_name: '',
		repeatable: true



TextControlView = Backbone.View.extend

	templateID: 'fields-control-text-content'

	template: ( data ) -> wp.template( @templateID )( data )

	render: -> this.template( @model.toJSON() )



RepeaterView = Backbone.View.extend

	el: '.fields-control-repeater'

	events:
		'click .add-field' : 'addField'

	addField: ( event ) ->

		event.preventDefault()

		newField = new TextControlView( model: new TextControlModel )
		@$el.append( "<br/> #{ newField.render() }" )



new RepeaterView
