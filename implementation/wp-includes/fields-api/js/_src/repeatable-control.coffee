TextControlModel = Backbone.Model.extend
	defaults: ->
		type:       'text'
		value:      ''
		input_id:   "field-general-example_my_1_repeater_field_#{@cid}"
		input_name: "general-example_my_1_repeater_field_#{@cid}"



TextControlView = Backbone.View.extend

	tagName: 'input'

	templateID: 'fields-control-text-content'

	template: ( data ) -> wp.template( @templateID )( data )

	render: -> this.template( @model.toJSON() )

	initialize: ->
		this.listenTo @model, 'destroy', this.remove
		this.render()




RepeaterView = Backbone.View.extend

	el: '.fields-control-repeater'

	events:
		'click .add-field' : 'addField'

	addField: ( event ) ->

		event.preventDefault()

		newField = new TextControlView( model: new TextControlModel )

		@$el.append( '<br>' )
		@$el.append( newField.render() )




new RepeaterView
