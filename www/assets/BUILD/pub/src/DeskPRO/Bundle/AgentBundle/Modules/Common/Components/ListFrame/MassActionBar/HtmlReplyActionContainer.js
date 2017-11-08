import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import RteEditor from 'DeskPRO/Component/Rte/RteEditor';

@connect()

export class HtmlReplyActionContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object
  };

  componentWillMount() {
    const { currentParams } = this.props;
    const message     = currentParams ? currentParams.get('message') : '';
    const isAgentNote = currentParams ? currentParams.get('isAgentNote') : false;

    this.state = { message, isAgentNote };
  }

  onChangeMessage = (text) => {
    this.setState({ message: text });
  };

  onReset = (event) => {
    event.preventDefault();
    const { dispatch, resetSingleAction } = this.props;
    dispatch(resetSingleAction('reply'));
    this.setState({ message: '', isAgentNote: false });
  };

  onSubmit = (event) => {
    event.preventDefault();
    const { dispatch, setParams } = this.props;
    dispatch(setParams({ reply: { message: this.state.message, isAgentNote: this.state.isAgentNote } }));
  };

  setIsAgentNote = (event) => {
    event.preventDefault();
    this.setState({ isAgentNote: !this.state.isAgentNote });
  };

  render() {
    return (
      <div className="dpw-navigation-dropdown-panel" style={{ width: '440px' }}>
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw-navigation-dropdown-reply-controls">
                <a href="#set-is-note" className="add-agent-note" onClick={this.setIsAgentNote}>
                  <span className="dpw--checkbox-boxy">
                    <i className={classNames('fa', { 'fa-check': this.state.isAgentNote })} />
                  </span>
                  <span className="control-text">Agent Note</span>
                </a>
              </div>
              <div className="dpw-navigation-dropdown-reply-item-reply">
                <RteEditor
                  inline
                  value={this.state.message}
                  onChange={this.onChangeMessage}
                  className="textarea"
                  onPasteImage={this.onPasteImage}
                  options={{
                    autoLink:      true,
                    imageDragging: true,
                    placeholder:   { text: 'Type a message' },
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
              {this.state.message &&
                <div className="dpw-navigation-dropdown-reply-item-reply-footer">
                  <a href="#submit" className="dpw--panel-button" onClick={this.onSubmit}>OK</a>
                  &nbsp;
                  <a href="#reset" className="dpw--panel-button" onClick={this.onReset}>Reset</a>
                </div>
              }
            </div>
          </div>
        </div>
      </div>
    );
  }
}
