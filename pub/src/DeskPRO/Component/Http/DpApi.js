import Http from "./Http"

class DpApi extends Http {
  init() {

    // This makes it so the first value passed
    // to the resolved func is the data, makes it slightly easier
    // to use the api
    this.addResultResolver({
      /**
       * @param {HttpResponse} res
       */
      response: (res) {
        return [res.getData(), res];
      }
    });
  }
}
