export const AJAXSubmit = (function () {
  function submitData(oData, config) {
    /* the AJAX request... */
    const oAjaxReq = new XMLHttpRequest();
    oAjaxReq.submittedData = oData;

    if (config.updateProgress) {
      oAjaxReq.addEventListener('progress', config.updateProgress);
    }
    if (config.transferComplete) {
      oAjaxReq.addEventListener('load', config.transferComplete);
    }
    if (config.transferFailed) {
      oAjaxReq.addEventListener('error', config.transferFailed);
    }
    if (config.transferCanceled) {
      oAjaxReq.addEventListener('abort', config.transferCanceled);
    }

    oAjaxReq.withCredentials = true;
    /* method is POST */
    oAjaxReq.responseType = 'json';
    oAjaxReq.open('post', oData.receiver, true);
    /* enctype is multipart/form-data */
    const sBoundary = `---------------------------${Date.now().toString(16)}`;
    oAjaxReq.setRequestHeader('Content-Type', `multipart/form-data; boundary=${sBoundary}`);
    oAjaxReq.sendAsBinary(`--${sBoundary}\r\n${
      oData.segments.join(`--${sBoundary}\r\n`)}--${sBoundary}--\r\n`);
  }

  function processStatus(oData, config = {}) {
    if (oData.status > 0) { return; }
    /* the form is now totally serialized! do something before sending it to the server... */
    // /* doSomething(oData); */
    // /* console.log("AJAXSubmit - The form is now serialized. Submitting..."); */
    submitData(oData, config);
  }

  function pushSegment(oFREvt) {
    this.owner.segments[this.segmentIdx] += `${oFREvt.target.result}\r\n`;
    this.owner.status = this.owner.status - 1;
    processStatus(this.owner, this.config);
  }

  function SubmitRequest(config) {
    this.receiver = config.url;
    this.status = 0;
    this.segments = [];
    for (let nFile = 0; nFile < config.files.length; nFile++) {
      const oFile = config.files[nFile];
      const oSegmReq = new FileReader();
      /* (custom properties:) */
      oSegmReq.segmentIdx = this.segments.length;
      oSegmReq.owner = this;
      oSegmReq.config = config;
      /* (end of custom properties) */
      oSegmReq.onload = pushSegment;
      this.segments.push(`Content-Disposition: form-data; name="file[${
        config.name}]"; filename="${oFile.name}"\r\nContent-Type: ${oFile.type}\r\n\r\n`);
      this.status = this.status + 1;
      oSegmReq.readAsBinaryString(oFile);
    }
    this.segments.push(`Content-Disposition: form-data; name="file[_dp_csrf_token]"\r\n\r\n${config.token}\r\n`);
    processStatus(this, config);
  }

  // eslint-disable-next-line func-names
  return function (config) {
    if (!config.url) { return; }
    this.req = new SubmitRequest(config);
  };
}());
