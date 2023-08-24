# placeholder for the new proposal for make.wp.org

[Original Proposal](https://make.wordpress.org/core/2016/03/14/fields-api-where-were-at/)

As WordPress has evolved over the years and at the hands of thousands (millions?) of contributors, there are naturally needs to integrate structures that have been defined and developed independently without any awareness of one another.  While this is part of the beauty of the WP ecosystem, we have reached a critical point at which abstraction and normalization of certain elements are necessary in order to leverage the true depth and breadth of the WP world as well as maintain and increase the velocity of progress.  

Our attention is initially focused on the centralized declaration and registration of fields at the Core to enable all plug-ins to interact seamlessly with your application's data structures without a need for heavy-handed customization.  This centralization will take the form of a native data contract structure that enables a single source of truth for 
* definition of data type
* capture of metadata
* rules of engagement

One of the felt pain points of WP development is the current need to customize the integration of each and every plug-in for each and every implementation.  By registering your fields at the Core, they will be available to all native functions to interrogate and ingest as well as any Core-compliant plug-ins that adopt this methodology.  

We recognize that a significant portion of the WP ecosystem lives outside of Core - while that has created the need for this centralization, it is also a barrier to entry in that existing plug-ins will need to adopt this integration model.  We believe that the one-time effort to adapt to this common language for data interaction will be more than returned in the new reality that 
* developers no longer need to evaluate cross-plug-in compatibility
* plug-ins will be able to interact seamlessly without any additional effort
* adjustments to the core data contract will be available across an implementation without needing to perform maintenance at each integration point
