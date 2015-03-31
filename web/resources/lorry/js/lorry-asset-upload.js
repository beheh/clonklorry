var assetResumable = new Resumable({target: base + '/publish/' + addon + '/' + release + '/upload?type=asset', permanentErrors: [403, 404, 415, 500, 501], query: {state: state}});
