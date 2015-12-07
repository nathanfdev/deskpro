import React, { PropTypes } from 'react';
import Positioned from 'DeskPRO/Component/Positioned/Detached';
import RteInput from 'DeskPRO/Component/Rte/RteInput';
import EmotionButton from 'DeskPRO/Component/Rte/EmotionButton';

export class Footer extends React.Component {

  static propTypes = {
    handleAddMessage: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      message: '',
      emoticonsOpened: false
    };
  }

  toggleEmoticons = () => {
    this.setState({emoticonsOpened: !this.state.emoticonsOpened});
  };

  handleChange = (text) => {
    this.setState({message: text});
  };

  handleSubmit = event => {
    event.preventDefault();

    this.props.handleAddMessage(this.state.message);
    this.setState({
      message: ''
    });
  };

  insertEmoticon = (number) => {
    // OMG!
    let str = this.refs.textarea.innerHTML;
    if (str.trim().substr(0, 3) !== '<p>') {
      str += '<p>';
    }
    str = str.substr(0, str.length - 4);
    str += this.renderEmoticon(number) + '</p>';
    this.medium.setContent(str);
  };

  renderEmoticon(number) {
    const className = 'emoticon sprite sprite-emoticon-' + number;
    return '&nbsp;<img src="" class="' + className + '"/>&nbsp;';
  }

  renderEmoticonsTable = () => {
    return (
      <Positioned
        positionMy="left-18px top+5px"
        positionAt="center bottom"
        positionTarget={this.refs.emoticonsButton}
        isOpen={this.state.emoticonsOpened}
        >
        <div id="emoticon-panel" className="emoticon-panel">
          <div>
            <a onClick={this.insertEmoticon.bind(null, 1)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-1"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 2)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-2"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 3)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-3"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 4)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-4"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 5)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-5"></span></a>
          </div>

          <div>
            <a onClick={this.insertEmoticon.bind(null, 6)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-6"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 7)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-7"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 8)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-8"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 9)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-9"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 10)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-10"></span></a>
          </div>

          <div>
            <a onClick={this.insertEmoticon.bind(null, 11)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-11"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 12)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-12"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 13)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-13"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 14)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-14"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 15)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-15"></span></a>
          </div>

          <div>
            <a onClick={this.insertEmoticon.bind(null, 16)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-16"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 17)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-17"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 18)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-18"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 19)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-19"></span></a>
            <a onClick={this.insertEmoticon.bind(null, 20)} className="emoticon-link"><span className="emoticon sprite sprite-emoticon-20"></span></a>
          </div>
        </div>
      </Positioned>
      );
  };

  render() {
    return (
      <footer>
        <form onSubmit={this.handleSubmit}>
          <RteInput
            inline
            ref="editor"
            value={this.state.message}
            onChange={this.handleChange}
            onSubmit={this.handleSubmit}
            className="textarea"
            options={{
              autoLink: true,
              imageDragging: true,
              placeholder: {
                text: 'Send a message'
              },
              toolbar: {
                buttons: ['bold', 'italic', 'underline', 'anchor'],
                updateOnEmptySelection: true
              },
              paste: {
                forcePlainText: false,
                cleanPastedHTML: false,
                cleanAttrs: ['style', 'dir']
              }
            }}
            />
          <EmotionButton
            buttonClassName="emoticon sprite sprite-emoticon-1"
            className="insert-emoticon"
            getEditor={() => this.refs.editor.getMediumEditor()}
            />
          <input onClick={this.handleSubmit} type="button" value="&#xf101;"/>
        </form>
      </footer>
    );
  }
}