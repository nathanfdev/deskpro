import React, { PropTypes } from 'react';
import TextareaAutosize from 'react-textarea-autosize';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import Editor from 'react-medium-editor';

export class Footer extends React.Component {

  static propTypes = {
    handleAddMessage: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      emojiOpened: false,
      message: ''
    };
  }

  toggleEmoji = () => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.emojiOpened = !oldState.emojiOpened;
    this.setState(newState);
  };

  handleChange(text) {
    const oldState = this.state;
    const newState = {...oldState};
    newState.message = text;
    this.setState(newState);
  }

  handleTyping = (event) => {
    if (event.keyCode === 13 && event.altKey === true) {
      event.target.value += '\r\n';
      this.handleChange(event);
    } else if (event.keyCode === 13) {
      event.preventDefault();
      this.handleSubmit();
    }
  };

  handleSubmit = () => {
    if (this.state.message) {
      this.props.handleAddMessage(this.state.message);
      this.handleChange('<p><br/></p>');
    }
  };

  renderEmojiTable = () => {
    return (
      <Positioned
        positionMy="left-18px top+5px"
        positionAt="center bottom"
        positionTarget={this.refs.emojiButton}
        isOpen={this.state.emojiOpened}
        >
        <div className="emoticon-panel">
          <div>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-1"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-2"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-3"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-4"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-5"></span></a>
          </div>

          <div>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-6"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-7"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-8"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-9"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-10"></span></a>
          </div>

          <div>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-11"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-12"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-13"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-14"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-15"></span></a>
          </div>

          <div>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-16"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-17"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-18"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-19"></span></a>
            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-20"></span></a>
          </div>
        </div>
      </Positioned>
      );
  };

  render() {
    const options = {
      placeholder: {
        text: 'Send a message'
      },
      anchorPrview: false,
      toolbar: {
        buttons: ['bold', 'italic', 'underline'],
        updateOnEmptySelection: true
      }
    };
    return (
      <footer>
        <form onSubmit={this.handleSubmit}>
          <Editor
            tag="div"
            className="textarea"
            text={this.state.message}
            onChange={this.handleChange.bind(this)}
            options={options}
          />
          <a href="#" ref="emojiButton" onClick={this.toggleEmoji} className="insert-emoticon"><span className="emoticon sprite sprite-emoticon-1"></span></a>
          <input onClick={this.handleSubmit} type="button" value="&#xf101;"/>
          { this.renderEmojiTable() }
        </form>
      </footer>
    );
  }
}