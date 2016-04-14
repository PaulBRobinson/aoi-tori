# Setup

Setup for Twitter Stream was a bit of a chore. Anyone who used it will know of the awkward dance you had to complete with Twitter to grant access to your newly installed plugin. I have tried my best to make it as simple as possible to setup Aoi Tori. Hopefully you will find it much easier to get started.

## Create A Twitter App

First you will need to create a Twitter Application. Head to the [Twitter Application Management](https://apps.twitter.com/) website and click 'Create New App'. Enter details based on the following information:

- **Name:** The name for your application. Can be whatever you wish, but cannot include the word Twitter.
- **Description:** A short description for your application. Again, can be whatever you wish.
- **Website:** Just enter your home web address.
- **Callback URL:** Leave this field blank. If you fill this field in it may cause an error, please leave it blank.

Agree to the terms (after reading them, of course) and click 'Create your Twitter Application'. Click on the 'Keys and Access Tokens' tab and copy:

- Consumer Key
- Consumer Secret
- Access Token
- Access Token Secret

## To The WordPress Admin

Go to your WordPress Admin and head to Aoi Tori's options page, located under the Settings menu, click on the 'API Keys' tab and enter each key into its relevant field. Then don't forget to hit save.

After you save Aoi Tori will automatically check your keys by making a quick validation request to Twitter to make sure they are valid. This also serves as a check to make sure it can access Twitter's server. If you see green, you are good to go. If you see red, please check the Troubleshooting section of these documentations.
