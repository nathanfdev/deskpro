import React from "react"
import _ from "lodash"
import DpDropzone from "DeskPRO/Bundle/AppBundle/React/Standalone/DpDropzone"

export default class DropzoneUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {files:[]};
  }
  componentDidMount() {
    this.setState({
      files: []
    });
  }
  onDrop(files) {
    let file_set = this.state.files;
    //files = file_set.concat(files) - uncomment if you enable "multiple" in DpDropzone
    this.setState({
      files: files
    });
  }
  render() {
    return (
      <DpDropzone
        ref="dropzone"
        multiple={false}
        onDrop={this.onDrop.bind(this)}
        className="attach-file"
        activeClassName="attach-file-active"
        inputName={this.props.inputName}
        >
        <i className="fa fa-link"></i>
        <span>Click here to attach a file (or just drag &amp; drop a file here)</span>
        {this.renderPreview()}
      </DpDropzone>
    );
  }
  renderPreview() {
    if (this.state) {
       return (
          <div className="previews">
            {_.map(this.state.files, ((file) => {
              return (
                <div className="frame" key={file.name + file.preview}>
                  { file.name.match(/\.(jpg|jpeg|png|gif)$/) ?
                  <img src={file.preview} style={{height: "50px", width: "50px"}} />
                    : null }
                  <div className="caption">
                    <span>{file.name}</span>
                    <span>{file.size + ' bytes'}</span>
                  </div>
                </div>
              )
            }))}
          </div>
      );
    }
  }
}
