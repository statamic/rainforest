#!/bin/bash

# Copy site folder
cp -aR site/ rainforest

# Copy README
cp README.md rainforest

# Remove dev files
rm -rf site/themes/rainforest/node_modules

# Zip up the release
zip -r rainforest.zip rainforest

# Clean up
rm -rf rainforest
