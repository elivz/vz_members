VZ Members
==========

A fieldtype for Expression Engine 2.

VZ Members displays either a dropdown list or a group of checkboxes containing the members in one or more member groups. The allowed member groups can be set on a per-field basis, as can whether the user can select only one or any number of members. When adding a new entry, the user can select a member or members to associate the entry with.

Template Tags
-------------

### Single Tags ###

    {members_field}

Will output a pipe-delimited list of member ids. You can also use the `separator` parameter to separate them with something other than a pipe. For instance `{members_field separator=', '}` would output something like: `1, 4, 5, 8`.

    {members_field:names}

Will output a list of member screen names with a comma and space between each. You can also use the `separator` parameter to separate them with anything else. For instance `{members_field:names separator=' and '}` would output something like: `Bob Smith and Jane Doe and Jimmy Jones`.

    {members_field:is_allowed members="1|4" groups="3"}

Checks if the members selected in this entry are among the members or groups specified in the tag parameters. You can specify member ids and/or group ids and either one can be a pipe-delimited list. With EE 1.6, it can also be used as a tag pair, in which case the content between the tags will only be displayed if the selected members are among those specified in the tag. EE 2 does not, unfortunately, support that syntax so you must use the tag inside a conditional to get the same effect. For example, if you want to show a notice for every weblog entry where a super-admin was selected, use this code in EE 1.6: {members_field:is_allowed groups="1"}Super!{/members_field:is_allowed}. In EE 2, the equivalent would be: {if "{members_field:is_allowed groups="1"}"}Super!{/if}.

### Tag Pair ###

    {members_field}{id} - {screen_name}{/members_field}

If you need more control over the output, use the tag pair.

#### Optional Parameters ####

`orderby="id|username|screen_name|group_id"` - The column to use in ordering the output. Default is `id`.

`sort="asc|desc"` - Which order to sort in. Default is `asc`.

`backspace="2"` - Remove the last _x_ characters from the final iteration.

#### Variables ####

`id` - The id of the current member.

`username` - The login name of the current member.

`screen_name` - The screen name of the current member.

`group_id` - The id of the group to which the current member belongs.

`count` - The number of the current iteration.

`total_results` - The total number of members selected.

`switch="odd|even"` - Switch between multiple values each time through the loop.

Finding Entries by Selected Member
----------------------------------

If you want to find the entries that have had a particular member assigned to them, you can use the [search parameter](http://ellislab.com/expressionengine/user-guide/add-ons/channel/channel_entries.html#search-field-name) of the Channel Entries tag pair with the `\W` option, like this:

    {exp:channel:entries channel="news" search:member_field="6\W"}
        ...
    {/exp:channel:entries}

In this example, `6` is the ID of the member you are looking for. The `\W` makes it match on the individual member IDs in the field, so it won't find partial matches (e.g. you don't want `6` to match member ID `16`).

Installation
------------

Download and unzip the extension. Upload the files, following the folder structure in the download.