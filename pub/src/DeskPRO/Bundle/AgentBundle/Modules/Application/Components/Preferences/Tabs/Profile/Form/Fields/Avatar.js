import React from 'react';

export class Avatar extends React.Component {

  render() {
    return (
      <div className="bucket-column-last">
        <a href="#" className="button button-secondary user-avatar"><span style={{backgroundImage: 'url(./img/avatar2.png)'}}></span>Manage Avatar</a>

        <div className="avatar-crop" id="avatar-crop" style={{display: 'none'}}>
          <p>Click &amp; drag to crop your avatar</p>
          <div className="cropper-bucket">
            <img src="./img/avatar-sample.png" alt="Avatar Sample" />
          </div>
          <a href="#" className="crop">Crop &amp; Save Avatar</a>
          <a href="#" className="cancel">Or cancel &amp; discard your changes</a>
        </div>
      </div>
    );
  }
}
