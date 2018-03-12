import PropTypes from 'prop-types';
import React from 'react';
import { AssetPlayButton } from 'DeskPRO/Component/AudioWidget/PlayButton';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import VoiceTargetNameContainer from '../../Common/NumberTarget/VoiceTargetNameContainer';

class AutoAttendantHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Auto Attendant"
        description="Set up simple dialpad menus and audio to help direct your incoming calls."
        dividing
      />
    );
  }
}

class AutoAttendantList extends React.Component {

  static propTypes = {
    autoAttendants: PropTypes.object,
    onAddNew:       PropTypes.func,
    onEdit:         PropTypes.func
  };

  renderEmpty() {
    const { onAddNew } = this.props;

    return (
      <div className="page">
        <AutoAttendantHeader />

        <button className="ui primary button" onClick={onAddNew}>
          Add new
        </button>
      </div>
    );
  }

  renderTable() {
    const { autoAttendants, onAddNew, onEdit } = this.props;

    return (
      <div className="page">
        <button className="ui right floated basic button" onClick={onAddNew}>
          <i className="icon plus" />
          Add Auto Attendant
        </button>
        <AutoAttendantHeader />

        <div className="admin-list-table">
          <div className="row header">
            <div className="column name">Name</div>
            <div className="column auto-attendant-dial-numbers">Active dialpad targets</div>
          </div>
          {autoAttendants.toArray().map((autoAttendant, index) =>
            <AutoAttendantRow
              key={index}
              autoAttendant={autoAttendant}
              onEdit={onEdit}
            />
          )}
        </div>
      </div>
    );
  }

  render() {
    const { autoAttendants } = this.props;

    return autoAttendants && autoAttendants.size > 0 ? this.renderTable() : this.renderEmpty();
  }
}

class AutoAttendantRow extends React.Component {

  static propTypes = {
    autoAttendant: PropTypes.object,
    onEdit:        PropTypes.func
  };

  onEdit = (event) => {
    event.preventDefault();
    const { autoAttendant, onEdit } = this.props;

    onEdit(autoAttendant);
  };

  render() {
    const { autoAttendant } = this.props;
    const targets = autoAttendant.get('targets');

    return (
      <div className="row">
        <div className="info">
          <div className="column name">{autoAttendant.get('name')}</div>
          <div className="column auto-attendant-dial-numbers">
            {targets.keySeq().toArray().map(dialNumber =>
              <VoiceTargetNameContainer key={dialNumber} target={targets.get(dialNumber)}>
                <AutoAttendantDialNumber dialNumber={dialNumber} />
              </VoiceTargetNameContainer>
            )}
          </div>
          <div className="column press-options">
            <div className="multiple-press-options">
              {autoAttendant.get('allow_repeat_menu') &&
                <span className="press-option">
                  Press ‘*’ to repeat the menu <i className="icon checkmark" />
                </span>}
              {autoAttendant.get('allow_extension') &&
                <span className="press-option">
                  Press ‘#’ key to enter an extension <i className="icon checkmark" />
                </span>}
            </div>
          </div>

          <div className="column options-button">
            <a onClick={this.onEdit}>
              <i className="fa fa-gear" />
            </a>
          </div>
          {autoAttendant.get('audio_asset') &&
            <AssetPlayButton value={autoAttendant.get('audio_asset')}>
              <PlayButton />
            </AssetPlayButton>}
          <div style={{ clear: 'both' }} />
        </div>
      </div>
    );
  }
}

class AutoAttendantDialNumber extends React.Component {

  static propTypes = {
    targetName:         PropTypes.string,
    dialNumber:         PropTypes.object,
    onRedirectToTarget: PropTypes.func
  };

  render() {
    const { dialNumber, onRedirectToTarget, targetName } = this.props;
    return (
      <div className="dial-number" onClick={onRedirectToTarget}>
        {dialNumber}: {targetName || '...'}
      </div>
    );
  }
}

class PlayButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    return (
      <div className="column options-button">
        <a onClick={this.onClick}>
          <i className="play icon" />
        </a>
      </div>
    );
  }
}

export default AutoAttendantList;
