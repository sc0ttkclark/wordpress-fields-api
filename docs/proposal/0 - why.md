# Benefit of Core Fields API

[Original Proposal](https://make.wordpress.org/core/2016/03/14/fields-api-where-were-at/)

As WordPress (WP) has evolved over the years and at the hands of thousands (millions?) of contributors, there are naturally needs to integrate structures that have been defined and developed independently without any awareness of one another.  While this is part of the beauty of the WP ecosystem, we have reached a critical point at which abstraction and normalization of certain elements are necessary in order to leverage the true depth and breadth of the WP world as well as maintain and increase the velocity of progress.  

Our attention is initially focused on the centralized declaration and registration of fields at the core to enable all plugins to interact seamlessly with your application's data structures without a need for heavy-handed customization.  This centralization will take the form of a native specification that enables a single source of truth for 
* definition of data type
* capture of metadata
* rules of engagement

One of the felt pain points of WP development is the current need to customize the integration of each and every plugin for each and every implementation.  By registering your fields at the core, they will be available to all native functions to interrogate and ingest as well as any core-compliant plugins that adopt this methodology.  Similar to existing customization, the relevant data construct will be accessed at run-time in order to avoid any unnecessary overhead at init.

We recognize that a significant portion of the WP ecosystem lives outside of core - while that has created the need for this centralization, it is also a barrier to entry in that existing plugins will need to adopt this integration model.  We believe that the one-time effort to adapt to this common language for data interaction will be more than returned in the new reality that 
* Developers no longer need to evaluate cross-plugin compatibility
* Plugins, themes, and core will be able to interact seamlessly without any additional effort
* Adjustments to the core data contract will be available across an implementation without needing to perform maintenance at each integration point
