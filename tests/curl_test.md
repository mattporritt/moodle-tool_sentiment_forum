# cURL Tests #

The following cURL commands allow you to test the Natural Language Understanding API via IBM Bluemix directly. This is good for testing text analysis without using Moodle.

## Get Token ##
This plugin uses token authentication. This saves passing the username and password credentials with every call.

The following is based on the IBM documentation that can be found at the following location: `https://console.bluemix.net/docs/services/watson/getting-started-tokens.html#tokens-for-authentication`

**NOTE:** You will need your username and password from the IBM Bluemix console. Replace the username and password values (including removing the braces) with the acutal username and password.

To get your token via curl:

<pre><code>
curl -X GET --user {username}:{password} \
"https://gateway.watsonplatform.net/authorization/api/v1/token?url=https://gateway.watsonplatform.net/natural-language-understanding/api"
</code></pre>

This call will return a token in the form of:
<pre><code>
4pthPMBfGcOTyQVdPPDRBowXux3TV2YPIBGjo%2FB2uvaPDv%2BOYWca6p2IYZp7q7DD1kuUL6FuYiNnfhIdMgVIs4N1xZuEIPtJos0U%
2BFhxQtayJ9OPulbz8ykJIYd8bc1GCGIJSzIq9h68Y8T50tsv%2Fp5WGJWx0Ym7oLuqwW%2BrkbchZBKrnrxIkojSQuTahkOLyQaM%2FG
69hF7oW9qryUsVCnmKjA8biB%2F7XhA2T0rXuRJM5oy%2F%2B5OEGAUulRO2K42tyTCRlmuZi%2FReKtVDChm6Yyz8PmWXdm9ov8mv2WZ
3TM0uNsfIwJ%2Fo8cLx8G9KdzJcwYzp2vVBXpL1loKlo6s1CRUKYHv5uskxWnKoTauKSywDR0oEQKqK6R0v6NPUiAcCDvY6KOAWJqiWPn
gxtTOqbDc1iCJz%2FU9nEby%2BqdNL6%2BhZHVRKEPvkDLMSYnzyjn08oLWSl4koXAj73pr4Rb46atVtoy9WiDjnMRqSpjFuofAwwTrle
IU9G%2BkgUtsLNqbKEcrLi9caqox7BpGc%2Bj%2FsOx%2FLdptTZ1XQQNjXHLbCwHshQHWfillYMaP3ewYkfQZQdPdaJKK6pxw%2B%2FX
TJqJTVq0Y9WU5rZWIu9O%2F%2F%2FjKU%2FQ9xpr%2BTjv9a831ucC2Rld%2Fn4mf6%2BP4O8cBlZ3dbVrvpgEFZVEgXopIBbCFfvp9ab
jldWDQsFtUShZloowB73gfLj5YcP6Rxq%2BH4CQdczYYtitmjVCI%2Bp64yGEnZiJtZO2hgiLGgEO%2ByMdKPd5k%2BL%2Fo8YlPz6whL
EedfZnpI%2B%2B7iWfDKcMuyWuFjQ5pluC2Dye4Q2ErtU76SeCLRCe%2B2lBXItY%2Fs7kjezx5HfciZ17hQFQHLuvF%2BpvljUIVtKET
GDR3K8Qumc8oWn2yT2Jo%2FyAnJWvfIomDwEzi5MGOPS8sz6slXGV4TjZRv64r35Js3LQLarVEIl9MDNngS1OnoWCjJPfjywVrO%2BBoz
WgAj5n5V7RgAwuI88BPyMVfSgCfh31n0DVUG91y4j3ZTIPudLB9A0AhGiDiPussgDpPbGouXOA%3D%3D
</code></pre>

This token can then be used for calls to the API.

Tokens have a time to live (TTL) of one hour, after which you can no longer use them to establish a connection with the service.


## Get Sentiment and Emotion ##

The following example will get basic sentiment and emotion of the submitted piece of text: `This service is fantastic.`

**NOTE:** Replace the `token` variable in the example (including removing the braces) with an actual generated token.

<pre><code>
curl -X POST \
-H "Content-Type: application/json" \
-H "Accept: application/json" \
-H "X-Watson-Authorization-Token: {token}" \
-d '{
  "text": "This service is fantastic.",
  "features": {
      "emotion": {},
      "sentiment":{}
    }
}' \
"https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27"
</code></pre>

This will return a response in the form of:

<pre><code>
{
  "usage": {
    "text_units": 1,
    "text_characters": 26,
    "features": 2
  },
  "sentiment": {
    "document": {
      "score": 0.900637,
      "label": "positive"
    }
  },
  "language": "en",
  "emotion": {
    "document": {
      "emotion": {
        "sadness": 0.019541,
        "joy": 0.788546,
        "fear": 9.9e-4,
        "disgust": 0.012258,
        "anger": 0.016462
      }
    }
  }
}
</code></pre>


## Get Sentiment, Emotion, Keywords and Concepts ##

The following example will get sentiment, emotion, keywords and concepts of the submitted piece of text: `This service is fantastic. I like programming a lot`

**NOTE:** Replace the `token` variable in the example (including removing the braces) with an actual generated token.

<pre><code>
curl -X POST \
-H "Content-Type: application/json" \
-H "Accept: application/json" \
-H "X-Watson-Authorization-Token: {token}" \
-d '{
  "text": "This service is fantastic. I like programming a lot",
  "features": {
      "emotion": {},
      "sentiment":{},
      "concepts":{
          "limit": 8
      },
      "keywords":{
          "limit": 8
      }
    }
}' \
"https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27"
</code></pre>

This will return a response in the form of:

<pre><code>
{
  "usage": {
    "text_units": 1,
    "text_characters": 51,
    "features": 4
  },
  "sentiment": {
    "document": {
      "score": 0.900637,
      "label": "positive"
    }
  },
  "language": "en",
  "keywords": [
    {
      "text": "service",
      "relevance": 0.912916
    }
  ],
  "emotion": {
    "document": {
      "emotion": {
        "sadness": 0.064248,
        "joy": 0.846788,
        "fear": 0.005968,
        "disgust": 0.014673,
        "anger": 0.021793
      }
    }
  },
  "concepts": [
    {
      "text": "Computer",
      "relevance": 0.853366,
      "dbpedia_resource": "http://dbpedia.org/resource/Computer"
    }
  ]
}
</code></pre>
