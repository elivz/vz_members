VZ Members
==========

A fieldtype for the [FieldFrame](http://brandon-kelly.com/fieldframe) extension

VZ Members displays either a dropdown list or a group of checkboxes containing the members in one or more member groups. The allowed member groups can be set on a per-field basis, as can whether the user can select only one or any number of members. When adding a new entry, the user can select a member or members to associate the entry with.

Prerequisites
-------------

You must have a recent version of FieldFrame installed to use VZ Members.

Template Tags
-------------

### Single Tag ###

    {members_field}

Will output a pipe-delimited list of member ids. You can also use the separator parameter to separate them with something other than a pipe. For instance `{members_field separator=', '}` would output something like: `1, 4, 5, 8`.

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

`total` - The total number of members selected.

`switch="odd|even"` - Switch between multiple values each time through the loop.

Installation
------------

Download and unzip the extension. Upload the files, following the folder structure in the download. You simply need to enable the VZ Members fieldtype in FieldFrame's extension settings to be ready to go.