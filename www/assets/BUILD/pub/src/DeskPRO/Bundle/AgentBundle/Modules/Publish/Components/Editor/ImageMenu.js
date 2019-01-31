import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import DropZone from 'DeskPRO/Component/Uploader/DropZone';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import { Input } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';

class ImageMenu extends React.Component {
  static propTypes = {
    insertImage: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      title:  '',
      width:  '',
      height: '',
      image:  null,
    };
  }

  getUploadUrl = () => '/api/v2/blobs/froala';

  setTitle = (title) => {
    this.setState({
      title
    });
  };

  setWidth = (width) => {
    this.setState({
      width
    });
  };

  setHeight = (height) => {
    this.setState({
      height
    });
  };

  setAlt = (alt) => {
    this.setState({
      alt
    });
  };

  handleSuccess = (e, data) => {
    console.log(data.result.link);
    if (data) {
      const match = data.result.link.match(/[^/]+\/[^/]+$/);
      if (match) {
        const image = match[0];
        this.setState({
          image
        });
      }
    }
  };

  insert = () => {
    const { title, width, height, image, alt } = this.state;
    let img = '!';
    if (alt) {
      img += `[${alt}]`;
    } else {
      img += `[${image.match(/[^/]+$/)[0]}]`;
    }
    img += `({{ img(${image}) }}`;
    if (title) {
      img += ` "${title}"`;
    }
    if (width || height) {
      img += ` =${width}x${height}`;
    }
    img += ')';
    this.props.insertImage(img);
  };

  renderImage = () => {
    if (this.state.image) {
      return <img className="preview-image" src={`/file.php/${this.state.image}`} role="presentation" />;
    }
    return null;
  };

  render() {
    return (
      <div>
        <div className="header"><FormattedMessage id="agent.guides.add_image" /></div>
        <div className="description">
          <DropZone
            getExternalInput={() => this.uploadButton.input}
            uploadUrl={this.getUploadUrl()}
            onSend={this.onSend}
            onSuccess={this.handleSuccess}
            onFail={this.onFail}
            ref={(c) => { this.node = c; }}
          >
            <div className="drop-zone">
              <UploadButton
                id={'upload_topic_image'}
                ref={(c) => { this.uploadButton = c; }}
                className="upload_topic_image"
                name="file"
                onSend={this.onSend}
                onSuccess={this.handleSuccess}
                onFail={this.onFail}
                uploadUrl={this.getUploadUrl()}
              />
              <label className="ui button small basic" htmlFor={'upload_topic_image'}>
                <FormattedMessage id="agent.general.upload_image" />
              </label>
            </div>
          </DropZone>
          {this.renderImage()}
          <br />
          <label htmlFor="image_title"><FormattedMessage id="agent.general.title" />: </label><br />
          <Input type="text" id="image_title" value={this.state.title} onChange={this.setTitle} /><br />
          <label htmlFor="image_width"><FormattedMessage id="agent.general.width" />: </label><br />
          <Input type="text" id="image_width" value={this.state.width} onChange={this.setWidth} /><br />
          <label htmlFor="image_height"><FormattedMessage id="agent.general.height" />: </label><br />
          <Input type="text" id="image_height" value={this.state.height} onChange={this.setHeight} /><br />
          <label htmlFor="image_alt"><FormattedMessage id="agent.guides.alternative_text" />: </label><br />
          <Input type="text" id="image_alt" value={this.state.alt} onChange={this.setAlt} /><br />
          <br />
          <Button onClick={this.insert} disabled={!(this.state.image)}>
            <FormattedMessage id="agent.general.insert" />
          </Button>
          <br />
          <div className="drop-info"><FormattedMessage id="agent.guides.images_drop_info" /></div>
        </div>
      </div>
    );
  }
}
export default ImageMenu;
