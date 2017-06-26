The leaflet_rs plugin transforms the home page into a searchable leaflet map
which displays resources that have latitude, longitude, and a positive resource
ID in their metadata.

**************************** UI CONFIGURATION **********************************
HOME PAGE TILES
Although the ResourceSpace config.php file has the home page tiles set not to
display, those switches do not work in this version of ResourceSpace and the
tiles must be manually dragged and dropped to the left of the screen. Then,
select "delete for all users" in the dialog box.

NAVIGATION BAR
The text for options in the navigation header can be managed in:
Admin > System > Manage content. The "search text" option is used to locate the
desired text. Select American English as the language when making changes, as
the language selection must match the default language of the installation.
Recommended Changes:
- Search -> Search Proposals
- Home -> Map of Proposals
-* Please wait... -> Please refresh...
*Please wait is a misnomer as the page has generally already stopped loading
and only a refresh will allow normal use to resume

GUEST ACCESS - NO LOGIN
For anonymous access without login, a user called "guest" must be added to a
user group "Guest".
The guest UI can be modified in Admin > System > Manage User Groups. Then,
Tools > Edit > Launch Permissions Manager.
Recommended Options:
> Search / Access
  - Search Capability
> Metadata Fields
  - UNCHECK: Can see all fields
  - CHECK: Can see field ______
  - UNCHECK/DO NOT CHECK: Can see field 'Email'
> Resource Types
  - Can see resource type ______

USER INTERFACE
In Admin > System > System Configuration
> Navigation:
  - "Show 'Featured collections' line" -> "No".
> User interface:
  *- "Basic simple search" -> "Enable"
* The regular simple search can be used with this plugin, although additional
styling may be desired. There is also a switch for this set to "true" in the
config.php ($basic_simple_search), which does not appear to work. Also the
button with html id #Rssearchexpand needs to be shown in order to have the
option to access advanced search.

ABOUT US
The about us text and html can be edited in Admin > System > Manage Content,
then search text by "about".

METADATA FIELDS
- Change Name (stored in Resource and Resource_Data tables)
> Country (field3) -> Zipcode (field3)
- Add New (stored in Resource_Data tables)
> fields 84-90 as global fields for all resource types
    (social handles, media , age, email, credit)
> fields 91-93 as Monument Lab Research Form fields
    (transcription, keywords (checkboxes), additional keywords (textbox entry)
> field 94 as global field workflow status for unprocessed, mapped, and
    transcribed. Note that the "Add single resource" link under
    Admin > Manage Resources does not include the default values for dropdowns.
    All other upload forms on the ResourceSpace site do respect the default
    values. No workflow status needs to be provided for resources uploaded via
    the dropbox script will default, as they will default to unprocessed.
- Remove
> keywords
> keywords (other)
> caption

*********************** CUSTOMIZING IMAGES & ASSETS ****************************
HOME PAGE BACKGROUND
The home page background color can be changed by overwriting the file 1.jpg in
the leaflet_rs/leaflet_homeanim folder with another solid color image.

FAVICON
The favicon can be changed by swapping the file in leaflet_rs/favicon.

LOGOS
Add images to leaflet_rs/assets and update the filename in the relevant setting
in resourcespace/include/config.php.

SOCIAL MEDIA ICONS
Add images to leaflet_rs/assets and update the filename in the relevant image
path in hooks/home.php.

****************************** MAP OPTIONS *************************************
BOUNDS
To change the bounds of the map, use map.fitBounds() on line 118.
Currently the bounds of the map are set to the neighborhood layer.
These can be changed to something else using
L.map('leaflet_rs_map').setView([39.9526, -75.1652], 5), where the values in
brackets are lat and long and the other number is the zoom.

LAYERS
- Neighborhoods
The neighborhoods layer is in the file leaflet_rs/js/neighborhoods.js as a
GeoJSON variable and is available on Azavea’s Github.
- Styles
The neighborhoods layer and the markers layer can be styled in:
leaflet_rs/hooks/home.php. The styling of the neighborhoods layer is in the
variable neighborhoodsStyle. The styling of the marker layer is in the variable
geojsonMarkerOptions.

POP-UPS
The content of the popup can be changed in the "layer.bindPopup" in the
javascript contained in leaflet_rs/hooks/home.php.

LIBRARIES
There are two different versions of the leaflet library that are imported.
Both are necessary for the map.

LEAFLET PLUG-INS
The MarkerCluster plugin is used to enable marker clustering. Its style data
is found in the files MarkerCluster.css (color and opacity) and
MarkerCluster.Default.css. The main script is in leaflet.markercluster-src.js
and leaflet.markercluster.js. The plugin is available at:
https://github.com/Leaflet/Leaflet.markercluster.

******************************** NOTES *****************************************
DELETION
Delete is a workflow state, and permanent deletion requires follow-up in
Admin > Manage Resources > View Deleted Resources.

KEYWORD AMBIGUITY
- LEAFLET KEYWORDS/ RESOURCESPACE NODES
The keywords in the selector on the map are from metadata checkbox files
(called keywords) and are stored in the nodes table in the database. This
selector dynamically populates so the keywords can be changed with no effect on
functionality. A node can contain spaces and punctuation, such as "Digital
Project".
- RESOURCESPACE KEYWORDS
The search bar results are based on the "keywords" table in the database which
are generated by ResourceSpace based on title, caption, keyword (checkboxes),
location, and other metadata fields. The checkbox keywords (nodes), are also in
this table. An example entry in the nodes tables would be "Digital Project". The
keywords table would have two entries based on this metadata field: "Digital"
and "Project".

RESOURCE TYPES
Resource types can be altered in Admin > System > Manage resource types.
The smoothest way to achieve customization is to edit existing resource types.
Removing the video and audio resource types makes upload and edit options more
straightforward, but otherwise does not affect the performance of this plugin.
Resource types being used can be assigned more specific aliases, for example:
"Photo" -> "Monument Lab Research Form (scanned photo)". Resource types can
also be assigned additional metadata fields that will show up in a separate
minimizable section on the upload and edit forms. The metadata fields keyword
checkboxes, location transcription text box, and transcription text box can be
added this way for the Monument Lab Research Form resource type.

RESEARCH ID URL & DIRECT_VIEW PAGE
The page "direct_view.php" can be accessed with a URL containing only the
research ID with the form:
"[baseURL]/plugins/leaflet_rs/pages/direct_view.php?ID=[researchID]".

BACK NAVIGATION
Due to ResourceSpace's native issues with back-navigation and javascript
initialization, leaflet_rs forces any link to the homepage to reload the entire
homepage. This maximizes the user experience by preventing the user from having
to manually refresh, and ensures that the full map functionality is available
no matter where the user navigates from.
> Alternative Configuration: To maintain the full functionality of the map
without forcing a reload upon every navigation to the home page, comment out
the current content of all.php and move the javascript libraries and leaflet
styles, with the exception of those related to the geocoder, from home.php to
the all.php file. The geocoder works best when its libraries are loaded in
in the hook HookLeaflet_rsHomeFooterbottom. Moving the other libraries leaves
leaflet_rs fully functional, but users may have to manually refresh the homepage
depending on where they navigate from. In this case it is strongly recommended to
configure the loading box text as indicated above from "Please wait..." to
"Please refresh" as the page will never finish loading and there is nothing for
the user to wait for.
