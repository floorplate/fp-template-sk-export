---
id: 4237b452-efe4-4b18-832d-10b55d3ebd70
blueprint: page
title: 'Demo Page'
nav_style: dark
template: default
content_blocks:
  -
    id: fpdemo02
    mobile_aspect_video: false
    media_type:
      -
        id: fpdemo01
        type: vimeo_video_embed
        enabled: true
        vimeo_embed_link: 'asset::assets::videos/demo/tower-timelapse.mp4'
    type: large_media_hero
    enabled: true
  -
    id: fpdemo06
    text_color: light
    anchor_id: introduction
    text_content:
      -
        type: heading
        attrs:
          textAlign: center
          level: 2
          font-weight: '400'
        content:
          -
            type: text
            text: 'Everything on this page is '
          -
            type: text
            marks:
              -
                type: btsSpan
                attrs:
                  class: text-accent-text-color
            text: 'yours to change.'
      -
        type: paragraph
        attrs:
          textAlign: center
        content:
          -
            type: text
            text: 'This demo walks through every content block that ships with Floorplate, and the settings that change how each one looks. Add blocks to any page from the '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Content Blocks'
          -
            type: text
            text: ' field, then drag to reorder, duplicate, hide or delete them.'
      -
        type: paragraph
        attrs:
          textAlign: center
        content:
          -
            type: text
            text: 'The video above is the Large Media Hero set to a looping video. Colors, fonts and logos live in '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Globals → Site Theme'
          -
            type: text
            text: '. Change them there and every block on every page follows.'
      -
        type: set
        attrs:
          id: fpdemo03
          values:
            type: layout_spacer
      -
        type: set
        attrs:
          id: fpdemo04
          values:
            type: button_types
            button_types:
              -
                id: fpdemo05
                button_text: 'Open the control panel'
                button_link: /cp
                open_in_new_tab: false
                type: no_outline_button
                enabled: true
            alignment: center
            text_color: accent
    type: intro_text
    enabled: true
  -
    id: fpdemo07
    anchor_background_color: '#F0F3F6'
    active_item_background_color: '#FFFFFF'
    text_color: '#0B1422'
    type: anchor_nav
    enabled: true
  -
    id: fpdemo08
    text_color: dark
    anchor_id: text
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Text Block'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The workhorse of the kit. The rich text editor gives you headings from H1 to H6, '
          -
            type: text
            marks:
              -
                type: bold
            text: bold
          -
            type: text
            text: ', '
          -
            type: text
            marks:
              -
                type: italic
            text: italic
          -
            type: text
            text: ', left/center/right alignment, bulleted lists and in-page anchor links. Two text styles pick up your theme colors: '
          -
            type: text
            marks:
              -
                type: btsSpan
                attrs:
                  class: text-accent-text-color
            text: accent
          -
            type: text
            text: ' and '
          -
            type: text
            marks:
              -
                type: btsSpan
                attrs:
                  class: text-secondary-text-color
            text: secondary
          -
            type: text
            text: .
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Inside any text area you can also drop in buttons, inline images and layout spacers, shown in the next two sections. Most blocks also take a background color override, a text color (dark, light, accent or secondary) and top/bottom spacing overrides.'
    type: text_block
    enabled: true
  -
    id: fpdemo0g
    background_color_override: '#F0F3F6'
    text_color: dark
    spacing_top_override: medium
    spacing_bottom_override: medium
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: Buttons
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Three kinds, each with its own alignment and color. This section also shows a '
          -
            type: text
            marks:
              -
                type: bold
            text: 'background color override'
          -
            type: text
            text: ' and '
          -
            type: text
            marks:
              -
                type: bold
            text: 'medium spacing'
          -
            type: text
            text: ' instead of the default.'
      -
        type: set
        attrs:
          id: fpdemo09
          values:
            type: layout_spacer
      -
        type: set
        attrs:
          id: fpdemo0a
          values:
            type: button_types
            button_types:
              -
                id: fpdemo0b
                button_text: 'A link button, aligned left'
                button_link: /cp
                open_in_new_tab: false
                type: no_outline_button
                enabled: true
            alignment: left
            text_color: accent
      -
        type: set
        attrs:
          id: fpdemo0c
          values:
            type: button_types
            button_types:
              -
                id: fpdemo0d
                button_text: 'A download button (sample brochure PDF), centered'
                download_asset: downloads/demo/sample-brochure.pdf
                type: download_button
                enabled: true
            alignment: center
            text_color: secondary
      -
        type: set
        attrs:
          id: fpdemo0e
          values:
            type: button_types
            button_types:
              -
                id: fpdemo0f
                button_text: 'An email button, aligned right'
                email_address: leasing@example.com
                type: email_button
                enabled: true
            alignment: right
            text_color: dark
    type: text_block
    enabled: true
  -
    id: fpdemo0k
    text_color: dark
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Inline Images'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Images placed inside a text area are sized as a percentage of the column and aligned left, middle or right. The corner rounding comes from '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Media Border Rounding'
          -
            type: text
            text: ' in Site Theme.'
      -
        type: set
        attrs:
          id: fpdemo0h
          values:
            type: inline_image
            image: floorplans/demo/sample-floor-plan.jpg
            width_percent: 100
            align: center
      -
        type: paragraph
        attrs:
          textAlign: center
        content:
          -
            type: text
            text: 'Full width, centered: a floor plan.'
      -
        type: set
        attrs:
          id: fpdemo0i
          values:
            type: inline_image
            image: photos/demo/lobby-atrium.jpg
            width_percent: 60
            align: left
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: '60% width, aligned left.'
      -
        type: set
        attrs:
          id: fpdemo0j
          values:
            type: inline_image
            image: photos/demo/rooftop-terrace.jpg
            width_percent: 40
            align: right
      -
        type: paragraph
        attrs:
          textAlign: right
        content:
          -
            type: text
            text: '40% width, aligned right.'
    type: text_block
    enabled: true
  -
    id: fpdemo0l
    background_color_override: '#F0F3F6'
    text_color: dark
    reverse_mobile_stack: false
    center_items_vertically: true
    anchor_id: columns
    first_text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Dual Column '
          -
            type: text
            marks:
              -
                type: btsSpan
                attrs:
                  class: text-accent-text-color
            text: Text
    second_text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Two rich text columns side by side, stacking on phones. This one has '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Center Items Vertically'
          -
            type: text
            text: ' turned on, so the short heading sits level with this longer paragraph.'
    type: dual_column_text_blocks
    enabled: true
  -
    id: fpdemo0n
    background_color_override: '#080F1A'
    text_color: light
    reverse_mobile_stack: true
    center_items_vertically: false
    first_text_content:
      -
        type: set
        attrs:
          id: fpdemo0m
          values:
            type: inline_image
            image: photos/demo/lounge.jpg
            width_percent: 100
            align: center
    second_text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 4
          font-weight: '400'
        content:
          -
            type: text
            text: 'Reverse Mobile Stack'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Here the image is in the first column, but '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Reverse Mobile Stack'
          -
            type: text
            text: ' is on, so on a phone this text comes first. '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Center Items Vertically'
          -
            type: text
            text: ' is off, so both columns align to the top.'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'It also shows a dark background override with light text.'
    type: dual_column_text_blocks
    enabled: true
  -
    id: fpdemo0u
    text_color: dark
    anchor_id: stats
    title: 'A few numbers about this kit'
    stats:
      -
        id: fpdemo0o
        type: stat
        enabled: true
        description: 'content blocks, all on this page'
        value:
          -
            id: fpdemo0p
            text: '15'
            type: text_value
            enabled: true
      -
        id: fpdemo0q
        type: stat
        enabled: true
        description: 'theme colors, set once in Site Theme'
        value:
          -
            id: fpdemo0r
            text: '6'
            type: text_value
            enabled: true
      -
        id: fpdemo0s
        type: stat
        enabled: true
        description: 'Google Fonts in the font pickers'
        value:
          -
            id: fpdemo0t
            text: '1,800+'
            type: text_value
            enabled: true
    type: stats
    enabled: true
  -
    id: fpdemo13
    background_color_override: '#F0F3F6'
    text_color: dark
    title: 'Stats can show an icon instead of a number'
    spacing_top_override: small
    stats:
      -
        id: fpdemo0v
        type: stat
        enabled: true
        description: 'Example: transit nearby'
        value:
          -
            id: fpdemo0w
            icon: icons/demo/transit.png
            type: icon_value
            enabled: true
      -
        id: fpdemo0x
        type: stat
        enabled: true
        description: 'Example: bike storage'
        value:
          -
            id: fpdemo0y
            icon: icons/demo/bike.png
            type: icon_value
            enabled: true
      -
        id: fpdemo0z
        type: stat
        enabled: true
        description: 'Example: sustainability'
        value:
          -
            id: fpdemo10
            icon: icons/demo/leaf.png
            type: icon_value
            enabled: true
      -
        id: fpdemo11
        type: stat
        enabled: true
        description: 'Example: on-site parking'
        value:
          -
            id: fpdemo12
            icon: icons/demo/parking.png
            type: icon_value
            enabled: true
    type: stats
    enabled: true
  -
    id: fpdemo14
    text_color: dark
    anchor_id: media
    spacing_bottom_override: small
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Images & Video'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Full Screen Media takes an image or a video. The first one below has a separate '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Mobile Override'
          -
            type: text
            text: ' image, so phones and tablets get a portrait crop (narrow your browser to see it swap). The second is a video with '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Playback Controls'
          -
            type: text
            text: ' turned on.'
    type: text_block
    enabled: true
  -
    id: fpdemo17
    main_media:
      -
        id: fpdemo15
        basic_image: photos/demo/exterior-towers.jpg
        type: basic_image
        enabled: true
    mobile_override_media:
      -
        id: fpdemo16
        basic_image: photos/demo/exterior-glass-tower-portrait.jpg
        type: basic_image
        enabled: true
    type: full_screen_media
    enabled: true
  -
    id: fpdemo19
    main_media:
      -
        id: fpdemo18
        vimeo_link: 'asset::assets::videos/demo/skyline.mp4'
        playback_controls: true
        type: vimeo_link
        enabled: true
    type: full_screen_media
    enabled: true
  -
    id: fpdemo1a
    text_color: dark
    anchor_id: carousels
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Amenity Carousel'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'A slideshow paired with copy. This one has the images on the '
          -
            type: text
            marks:
              -
                type: bold
            text: right
          -
            type: text
            text: ' and teal pagination.'
    carousel_images:
      - photos/demo/lounge-glass.jpg
      - photos/demo/fitness.jpg
      - photos/demo/rooftop-terrace.jpg
      - photos/demo/conference.jpg
    image_side: right
    pagination_color: '#14B8A6'
    type: amenity_carousel
    enabled: true
  -
    id: fpdemo1b
    background_color_override: '#080F1A'
    text_color: light
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Images on the '
          -
            type: text
            marks:
              -
                type: btsSpan
                attrs:
                  class: text-accent-text-color
            text: left
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The same block with '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Image Side'
          -
            type: text
            text: ' set to left, a dark background and white pagination.'
    carousel_images:
      - photos/demo/lobby-atrium.jpg
      - photos/demo/lobby-seating.jpg
      - photos/demo/lobby-hall.jpg
    image_side: left
    pagination_color: '#FFFFFF'
    type: amenity_carousel
    enabled: true
  -
    id: fpdemo1c
    text_color: dark
    anchor_id: galleries
    spacing_bottom_override: small
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: Galleries
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Dual Image Columns shows two images side by side. Masonry Images arranges 13 images in a fixed pattern: 4 small, 6 tall, 2 wide and 1 large.'
    type: text_block
    enabled: true
  -
    id: fpdemo1d
    image_one: photos/demo/lobby-entrance.jpg
    image_two: photos/demo/lounge-glass.jpg
    type: dual_image_columns
    enabled: true
  -
    id: fpdemo1e
    small_horizontal_rectangle_images:
      - photos/demo/facade-detail.jpg
      - photos/demo/conference.jpg
      - photos/demo/lobby-hall.jpg
      - photos/demo/facade-curve.jpg
    vertical_rectangle_images:
      - photos/demo/exterior-glass-tower.jpg
      - photos/demo/lobby-atrium.jpg
      - photos/demo/fitness.jpg
      - photos/demo/rooftop-terrace.jpg
      - photos/demo/lounge.jpg
      - photos/demo/lobby-seating.jpg
    long_horizontal_images:
      - photos/demo/skyline-dusk.jpg
      - photos/demo/exterior-night.jpg
    large_image: photos/demo/exterior-towers.jpg
    type: masonry_images
    enabled: true
  -
    id: fpdemo1l
    text_color: light
    numbered_rows: true
    anchor_id: customize
    expander_rows:
      -
        id: fpdemo1f
        title: Colors
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Six colors drive the whole site: primary and primary light (backgrounds), dark and light text, plus accent and secondary. Set them under '
              -
                type: text
                marks:
                  -
                    type: bold
                text: 'Globals → Site Theme'
              -
                type: text
                text: '. They start out as Floorplate’s own palette.'
        type: expander_row
        enabled: true
      -
        id: fpdemo1g
        title: Typography
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Pick a header font and a paragraph font, then fine-tune weight, size and line height. H1–H6, navigation and buttons can each override the defaults. Also in Site Theme.'
        type: expander_row
        enabled: true
      -
        id: fpdemo1h
        title: 'Logos and favicon'
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Upload a logo for dark and light navigation bars and a favicon in Site Theme; until you do, the Floorplate mark stands in. The footer logo, address and partner logos are under '
              -
                type: text
                marks:
                  -
                    type: bold
                text: 'Globals → Footer'
              -
                type: text
                text: .
        type: expander_row
        enabled: true
      -
        id: fpdemo1i
        title: Navigation
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Four menus: Main Center Nav, Main Right Nav (links can be shown as pill buttons), Footer Links and Footer Legal Links. Each page also chooses a dark or light nav style.'
        type: expander_row
        enabled: true
      -
        id: fpdemo1j
        title: 'Pages and SEO'
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Pages live under '
              -
                type: text
                marks:
                  -
                    type: bold
                text: 'Collections → Pages'
              -
                type: text
                text: '. Every page has an SEO tab for its title, description and social image, and site-wide defaults are under '
              -
                type: text
                marks:
                  -
                    type: bold
                text: 'SEO → Site Defaults'
              -
                type: text
                text: '. The homepage is the Home entry; add a content block to it and this site’s welcome screen is replaced by your page.'
        type: expander_row
        enabled: true
      -
        id: fpdemo1k
        title: Forms
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Contact and event forms come ready to use. Edit their fields and notification emails under '
              -
                type: text
                marks:
                  -
                    type: bold
                text: Forms
              -
                type: text
                text: ', then place one on any page with the Form block.'
        type: expander_row
        enabled: true
    type: expander_rows
    enabled: true
  -
    id: fpdemo1p
    background_color_override: '#FFFFFF'
    text_color: dark
    numbered_rows: false
    spacing_top_override: small
    expander_rows:
      -
        id: fpdemo1m
        title: 'The same block without numbers'
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Turn '
              -
                type: text
                marks:
                  -
                    type: bold
                text: 'Numbered Rows'
              -
                type: text
                text: ' off for FAQ-style rows. This one also has a white background and a small top spacing override.'
        type: expander_row
        enabled: true
      -
        id: fpdemo1n
        title: 'What do the spacing overrides do?'
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Every block has default padding above and below. The overrides swap it for none, small, medium, large or extra large, so you can tighten or open up the rhythm of a page without touching code.'
        type: expander_row
        enabled: true
      -
        id: fpdemo1o
        title: 'How do anchor links work?'
        text_content:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Give a block an Anchor ID and it becomes a jump target. The Anchor Nav near the top of this page lists every block that has one, and navigation links can point straight to them.'
        type: expander_row
        enabled: true
    type: expander_rows
    enabled: true
  -
    id: fpdemo1s
    text_color: light
    anchor_id: availabilities
    text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The Availabilities block lists every entry in the Availabilities collection: one per floor or suite, each with short detail columns and an expandable description. Open the 5th Floor to see a floor plan with download and email buttons.'
      -
        type: set
        attrs:
          id: fpdemo1q
          values:
            type: button_types
            button_types:
              -
                id: fpdemo1r
                button_text: 'Manage availabilities'
                button_link: /cp/collections/availabilities
                open_in_new_tab: false
                type: no_outline_button
                enabled: true
            alignment: left
            text_color: accent
    type: availabilities
    enabled: true
  -
    id: fpdemo1t
    text_color: dark
    anchor_id: location
    spacing_bottom_override: small
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: Maps
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The first map uses the '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Warm Topographic'
          -
            type: text
            text: ' style with its border and category sidebar. The second switches to '
          -
            type: text
            marks:
              -
                type: bold
            text: Dark
          -
            type: text
            text: ', turns the border off, hides the sidebar and sets a fixed '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Default Zoom'
          -
            type: text
            text: '. There are ten styles in all.'
    type: text_block
    enabled: true
  -
    id: fpdemo1z
    map_style: warm_topographic
    building_name: 'Your Building'
    building_pin_color: '#14B8A6'
    latitude: 41.8837
    longitude: -87.6324
    component_border: true
    hide_sidebar: false
    points_of_interest:
      -
        id: fpdemo1u
        category_name: Dining
        pin_color: '#D98E32'
        mobile_toggle_starts_on: true
        desktop_toggle_starts_on: true
        locations:
          -
            id: fpdemo1v
            name: 'Example café'
            latitude: 41.8851
            longitude: -87.63
            description: 'Points are grouped into categories, and each category gets its own pin color and on/off toggle.'
            type: point_of_interest
            enabled: true
          -
            id: fpdemo1w
            name: 'Example restaurant'
            latitude: 41.8815
            longitude: -87.6352
            description: 'Set the building’s coordinates, the map style and an optional custom pin icon in the Map block.'
            type: point_of_interest
            enabled: true
        type: poi_category
        enabled: true
      -
        id: fpdemo1x
        category_name: Transit
        pin_color: '#4976AC'
        mobile_toggle_starts_on: true
        desktop_toggle_starts_on: true
        locations:
          -
            id: fpdemo1y
            name: 'Example station'
            latitude: 41.8858
            longitude: -87.6341
            description: 'Add an optional View More link to any point.'
            type: point_of_interest
            enabled: true
        type: poi_category
        enabled: true
    type: map
    enabled: true
  -
    id: fpdemo25
    map_style: dark
    building_name: 'Your Building'
    building_pin_color: '#14B8A6'
    latitude: 41.8837
    longitude: -87.6324
    default_zoom: 15
    component_border: false
    hide_sidebar: true
    points_of_interest:
      -
        id: fpdemo20
        category_name: Dining
        pin_color: '#D98E32'
        mobile_toggle_starts_on: true
        desktop_toggle_starts_on: true
        locations:
          -
            id: fpdemo21
            name: 'Example café'
            latitude: 41.8851
            longitude: -87.63
            description: 'Points are grouped into categories, and each category gets its own pin color and on/off toggle.'
            type: point_of_interest
            enabled: true
          -
            id: fpdemo22
            name: 'Example restaurant'
            latitude: 41.8815
            longitude: -87.6352
            description: 'Set the building’s coordinates, the map style and an optional custom pin icon in the Map block.'
            type: point_of_interest
            enabled: true
        type: poi_category
        enabled: true
      -
        id: fpdemo23
        category_name: Transit
        pin_color: '#4976AC'
        mobile_toggle_starts_on: true
        desktop_toggle_starts_on: true
        locations:
          -
            id: fpdemo24
            name: 'Example station'
            latitude: 41.8858
            longitude: -87.6341
            description: 'Add an optional View More link to any point.'
            type: point_of_interest
            enabled: true
        type: poi_category
        enabled: true
    type: map
    enabled: true
  -
    id: fpdemo26
    text_color: dark
    anchor_id: forms
    selected_form: contact
    form_width: medium
    submit_text: 'Send Message'
    text_content:
      -
        type: heading
        attrs:
          textAlign: center
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: Form
      -
        type: paragraph
        attrs:
          textAlign: center
        content:
          -
            type: text
            text: 'The Contact form at '
          -
            type: text
            marks:
              -
                type: bold
            text: Medium
          -
            type: text
            text: ' width. Its fields and notification recipients are edited under '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Forms → Contact'
          -
            type: text
            text: ', and submissions show up there too.'
    type: form
    enabled: true
  -
    id: fpdemo27
    background_color_override: '#F0F3F6'
    text_color: dark
    selected_form: event
    form_width: wide
    submit_text: 'Request a Date'
    success_heading: 'Request received'
    success_message: 'This is a custom success message, set on the block.'
    text_content:
      -
        type: heading
        attrs:
          textAlign: center
          level: 3
          font-weight: '400'
        content:
          -
            type: text
            text: 'Event Form'
      -
        type: paragraph
        attrs:
          textAlign: center
        content:
          -
            type: text
            text: 'The Event form at '
          -
            type: text
            marks:
              -
                type: bold
            text: Wide
          -
            type: text
            text: ' width, with custom submit button text and success message. Forms can also be Narrow or Full Width.'
    type: form
    enabled: true
  -
    id: fpdemo2f
    text_color: light
    anchor_id: contact
    contact_sections:
      -
        id: fpdemo28
        section_title:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: 'Contact Sections'
        section_contacts:
          -
            id: fpdemo29
            type: contact
            enabled: true
            text_content:
              -
                type: heading
                attrs:
                  textAlign: left
                  level: 4
                  font-weight: '300'
                content:
                  -
                    type: text
                    text: 'Leasing Contact'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: 'Group contacts under a heading: leasing, management, press.'
              -
                type: set
                attrs:
                  id: fpdemo2a
                  values:
                    type: button_types
                    button_types:
                      -
                        id: fpdemo2b
                        button_text: leasing@example.com
                        email_address: leasing@example.com
                        type: email_button
                        enabled: true
                    alignment: left
                    text_color: accent
          -
            id: fpdemo2c
            type: contact
            enabled: true
            text_content:
              -
                type: heading
                attrs:
                  textAlign: left
                  level: 4
                  font-weight: '300'
                content:
                  -
                    type: text
                    text: 'Property Manager'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: 'Each contact is its own rich text area, so it can hold buttons like the email link below.'
              -
                type: set
                attrs:
                  id: fpdemo2d
                  values:
                    type: button_types
                    button_types:
                      -
                        id: fpdemo2e
                        button_text: manager@example.com
                        email_address: manager@example.com
                        type: email_button
                        enabled: true
                    alignment: left
                    text_color: accent
        type: contact_section
        enabled: true
    type: contact_sections
    enabled: true
---
