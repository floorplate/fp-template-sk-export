---
id: 375ecbbf-3789-49d9-8dea-13c3baef1939
blueprint: availability
title: '5th Floor'
text_columns:
  -
    id: fpdemo2g
    column_text: '12,400 RSF'
    type: text_column
    enabled: true
  -
    id: fpdemo2h
    column_text: 'Available now'
    type: text_column
    enabled: true
text_content:
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Example listing. Each availability opens to a rich text description, so it can hold a floor plan, photos and buttons like these.'
  -
    type: set
    attrs:
      id: fpdemo2i
      values:
        type: inline_image
        image: floorplans/demo/sample-floor-plan.jpg
        width_percent: 100
        align: left
  -
    type: set
    attrs:
      id: fpdemo2j
      values:
        type: button_types
        button_types:
          -
            id: fpdemo2k
            button_text: 'Download Floor Plan (PDF)'
            download_asset: floorplans/demo/sample-floor-plan.pdf
            type: download_button
            enabled: true
        alignment: left
        text_color: accent
  -
    type: set
    attrs:
      id: fpdemo2l
      values:
        type: button_types
        button_types:
          -
            id: fpdemo2m
            button_text: 'Ask about this space'
            email_address: leasing@example.com
            type: email_button
            enabled: true
        alignment: left
        text_color: accent
---
