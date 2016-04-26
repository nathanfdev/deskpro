import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { RteEditor } from 'DeskPRO/Component/Rte/RteEditor';
import { Popup } from '../../../../Common/Components/Popup';

@connect()

export class HtmlReplyActionContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object
  };

  render() {
    const { currentParams } = this.props;
    const message = currentParams ? currentParams.get('message') : '';
    return (
      <div className="dpw-navigation-dropdown-panel">
        <Popup>
          <form>
            <div className="dpw--popup-content">
              <div className="dpw--popup-content-line">
                <RteEditor
                  inline
                  ref="editor"
                  value={message}
                  onChange={this.onChangeMessage}
                  onSubmit={this.onSubmit}
                  className="textarea"
                  onPasteImage={this.onPasteImage}
                  options={{
                    autoLink:      true,
                    imageDragging: true,
                    placeholder:   { text: 'Send a message' },
                    toolbar:       {
                      buttons:                ['bold', 'italic', 'underline', 'anchor'],
                      updateOnEmptySelection: true
                    },
                    paste: {
                      forcePlainText:  false,
                      cleanPastedHTML: false,
                      cleanAttrs:      ['style', 'dir']
                    }
                  }}
                />
              </div>
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-left">
                  <a href="#" className="dpw--popup-button">OK</a>
                </div>
                <div className="dpw--popup-content-left">
                  <a href="#" className="dpw--popup-button">Reset</a>
                </div>
              </div>
            </div>
          </form>
        </Popup>
      </div>
    );
  }
}
