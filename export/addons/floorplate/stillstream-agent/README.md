# StillStream Agent

StillStream inside a site's Control Panel. The pages are the same ones
floorplate.io uses; this addon renders them and forwards every action to
Floorplate, signed with the site's `FLEET_SSO_SECRET` (the secret Fleet
Agent already holds). Credits, generation and rendering all happen on
Floorplate. The site's part is its asset container:

- photos are uploaded into `stillstream/{project}/uploads/` like any asset;
- clips, music and finished videos are written by Floorplate into the same
  bucket and added to the container here when a project is opened.

`resources/dist/build` is built from the StillStream addon in floorplate-io
(`npm run build:agent` there) and committed, so a site needs no build step.
