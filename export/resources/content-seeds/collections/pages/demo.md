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
        type: image
        enabled: true
        hero_image: placeholder_landscape.jpg
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
            text: 'This demo walks through every content block that ships with Floorplate. Each section below is one block: add them to any page from the '
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
            text: 'Colors, fonts and logos live in '
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
    anchor_id: text-block
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
            text: 'Inside any text area you can also drop in:'
      -
        type: bulletList
        content:
          -
            type: listItem
            content:
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    marks:
                      -
                        type: bold
                    text: Buttons
                  -
                    type: text
                    text: ' — link, file download or email, each with its own alignment and color.'
          -
            type: listItem
            content:
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    marks:
                      -
                        type: bold
                    text: 'Inline images'
                  -
                    type: text
                    text: ' — sized as a percentage of the column and aligned left, middle or right.'
          -
            type: listItem
            content:
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    marks:
                      -
                        type: bold
                    text: 'Layout spacers'
                  -
                    type: text
                    text: ' — a little breathing room between elements.'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Most blocks also take a background color override, a text color (dark, light, accent or secondary) and top/bottom spacing overrides.'
    type: text_block
    enabled: true
  -
    id: fpdemo09
    background_color_override: '#F0F3F6'
    text_color: dark
    reverse_mobile_stack: false
    center_items_vertically: true
    anchor_id: dual-columns
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
            text: 'Two rich text columns side by side, stacking on mobile. Flip the mobile order with '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Reverse Mobile Stack'
          -
            type: text
            text: ', and use '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Center Items Vertically'
          -
            type: text
            text: ' when one column is much shorter than the other — like this one.'
    type: dual_column_text_blocks
    enabled: true
  -
    id: fpdemo0g
    text_color: dark
    anchor_id: stats
    title: 'A few numbers about this kit'
    stats:
      -
        id: fpdemo0a
        value:
          -
            id: fpdemo0b
            text: '15'
            type: text_value
            enabled: true
        description: 'content blocks, all on this page'
        type: stat
        enabled: true
      -
        id: fpdemo0c
        value:
          -
            id: fpdemo0d
            text: '6'
            type: text_value
            enabled: true
        description: 'theme colors, set once in Site Theme'
        type: stat
        enabled: true
      -
        id: fpdemo0e
        value:
          -
            id: fpdemo0f
            text: '1,800+'
            type: text_value
            enabled: true
        description: 'Google Fonts in the font pickers'
        type: stat
        enabled: true
    type: stats
    enabled: true
  -
    id: fpdemo0j
    anchor_id: media
    main_media:
      -
        id: fpdemo0h
        basic_image: placeholder_landscape.jpg
        type: basic_image
        enabled: true
    mobile_override_media:
      -
        id: fpdemo0i
        basic_image: placeholder_1.jpg
        type: basic_image
        enabled: true
    type: full_screen_media
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
            text: 'Amenity Carousel'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'A slideshow paired with copy. Choose which side the images sit on and the color of the pagination dots. The Full Screen Media block above can also take a separate image for phones, so portrait screens get a better crop.'
    carousel_images:
      - placeholder_1.jpg
      - placeholder_2.jpg
      - placeholder_3.jpg
    image_side: right
    pagination_color: '#14B8A6'
    type: amenity_carousel
    enabled: true
  -
    id: fpdemo0l
    image_one: placeholder_2.jpg
    image_two: placeholder_3.jpg
    type: dual_image_columns
    enabled: true
  -
    id: fpdemo0m
    small_horizontal_rectangle_images:
      - placeholder_1.jpg
      - placeholder_2.jpg
      - placeholder_3.jpg
      - placeholder_4.jpg
    vertical_rectangle_images:
      - placeholder_1.jpg
      - placeholder_2.jpg
      - placeholder_3.jpg
      - placeholder_4.jpg
      - placeholder_5.jpg
      - placeholder_6.jpg
    long_horizontal_images:
      - placeholder_landscape.jpg
      - placeholder_1.jpg
    large_image: placeholder_landscape.jpg
    type: masonry_images
    enabled: true
  -
    id: fpdemo0t
    text_color: light
    numbered_rows: true
    anchor_id: customize
    expander_rows:
      -
        id: fpdemo0n
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
        id: fpdemo0o
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
        id: fpdemo0p
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
        id: fpdemo0q
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
        id: fpdemo0r
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
                text: '. Every page has an SEO tab for its title, description and social image. The homepage is the Home entry; add a content block to it and this site’s welcome screen is replaced by your page.'
        type: expander_row
        enabled: true
      -
        id: fpdemo0s
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
    id: fpdemo0w
    text_color: light
    anchor_id: availabilities
    spacing_top_override: none
    text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The Availabilities block lists every entry in the Availabilities collection — one per floor or suite, each with short detail columns and an expandable description.'
      -
        type: set
        attrs:
          id: fpdemo0u
          values:
            type: button_types
            button_types:
              -
                id: fpdemo0v
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
    id: fpdemo12
    anchor_id: location
    map_style: warm_topographic
    building_name: 'Your Building'
    building_pin_color: '#14B8A6'
    latitude: 41.8837
    longitude: -87.6324
    component_border: true
    hide_sidebar: false
    points_of_interest:
      -
        id: fpdemo0x
        category_name: Dining
        pin_color: '#D98E32'
        mobile_toggle_starts_on: true
        desktop_toggle_starts_on: true
        locations:
          -
            id: fpdemo0y
            name: 'Example café'
            latitude: 41.8851
            longitude: -87.63
            description: 'Points are grouped into categories, and each category gets its own pin color and on/off toggle.'
            type: point_of_interest
            enabled: true
          -
            id: fpdemo0z
            name: 'Example restaurant'
            latitude: 41.8815
            longitude: -87.6352
            description: 'Set the building’s coordinates, the map style and an optional custom pin icon in the Map block.'
            type: point_of_interest
            enabled: true
        type: poi_category
        enabled: true
      -
        id: fpdemo10
        category_name: Transit
        pin_color: '#4976AC'
        mobile_toggle_starts_on: true
        desktop_toggle_starts_on: true
        locations:
          -
            id: fpdemo11
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
    id: fpdemo13
    text_color: dark
    anchor_id: contact
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
            text: 'This is the Contact form. Its fields and notification recipients are edited under '
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
    id: fpdemo17
    text_color: light
    contact_sections:
      -
        id: fpdemo14
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
            id: fpdemo15
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
                    text: 'Group contacts under a heading — leasing, management, press.'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: leasing@example.com
          -
            id: fpdemo16
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
                    text: 'Each contact is its own rich text area, so add phone numbers, links or buttons.'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: manager@example.com
        type: contact_section
        enabled: true
    type: contact_sections
    enabled: true
---
