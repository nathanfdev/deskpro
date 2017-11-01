import PropTypes from 'prop-types';
import React from 'react';
import RteEditor from 'DeskPRO/Component/Rte/RteEditor';
import { EmotionButton } from 'DeskPRO/Component/Rte/EmotionButton';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';

export class Footer extends React.Component {

  static propTypes = {
    handleAddMessage: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      message:         '',
      emoticonsOpened: false
    };
  }

  handleChange = (text) => {
    this.setState({ message: text });
  };

  handleSubmit = (event) => {
    event.preventDefault();

    this.props.handleAddMessage(replaceSmileCodes(this.state.message, true));
    this.setState({ message: '' });
  };

  render() {
    return (
      <footer>
        <form onSubmit={this.handleSubmit}>
          <RteEditor
            inline
            ref={(e) => { this.refEditor = e; }}
            value={this.state.message}
            onChange={this.handleChange}
            onSubmit={this.handleSubmit}
            className="textarea"
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

          <EmotionButton
            buttonClassName="emoticon sprite sprite-emoticon-1"
            className="insert-emoticon"
            getEditor={() => this.refEditor}
            popupPositionAt="left-8 bottom+12"
            popupPositionMy="left top"
          />

          <input onClick={this.handleSubmit} type="button" value="&#xf101;" />
        </form>
      </footer>
    );
  }
}
