import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import classNames from 'classnames';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
export class PhrasesMenuContainer extends React.Component {
  static propTypes = {
    emailTemplates: PropTypes.object.isRequired,
    closeMenu:      PropTypes.func
  };

  selectPhrase = () => {
    this.props.closeMenu();
  };

  render() {
    let phrases = null;
    if (this.props.emailTemplates) {
      phrases = this.props.emailTemplates.get('phrases');
    }
    return (<PhrasesMenu
      phrases={phrases}
      onSelectPhrase={this.selectPhrase}
    />);
  }
}

export class PhrasesMenu extends React.Component {
  static propTypes = {
    phrases:        PropTypes.object,
    onSelectPhrase: PropTypes.func
  };

  static formatTitle(title) {
    const res = title.replace(/_/, ' ');
    return res.charAt(0).toUpperCase() + res.slice(1);
  }

  constructor(props) {
    super(props);
    this.state = {
      selectedLeft: null,
      filter:       ''
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      selectedLeft: nextProps.viewModel.first()
    });
  }

  setActive = (item) => {
    this.setState({
      selectedLeft: item
    });
  };

  getGroups = () => {
    if (!this.props.phrases) {
      return null;
    }
    return this.props.phrases.valueSeq().map((group, key) =>
      <MenuItem
        key={`phrase${key}`}
        label={PhrasesMenu.formatTitle(group.get('title'))}
        icon="folder open"
        className={classNames({ active: this.isActive(group) })}
        onClick={() => this.setActive(group)}
      />
    );
  };

  getRightPanel = () => {
    if (!this.state.selectedLeft || !this.state.selectedLeft.get('phrases')) {
      return null;
    }
    const properties = this.state.selectedLeft.get('phrases').valueSeq().map(
      (phrase, key) => {
        if (
          this.state.filter
          && phrase.get('phrase').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
          && phrase.get('key').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
          && this.state.selectedLeft.get('title').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
        ) {
          return null;
        }
        return (<MenuItem key={key} onClick={() => this.selectPhrase(phrase)}>
          <span className="phrase">{phrase.get('phrase')}</span>
          <br />
          <span className="phrase-key">
            {phrase.get('key')}
          </span>

        </MenuItem>);
      });
    return (
      <MenuWrapper className="right-panel">
        <Menu>
          {properties}
        </Menu>
      </MenuWrapper>
    );
  };

  getLeftFooter = () => (<footer>
    <div className="ui action input small">
      <button
        className={classNames(
            'ui button basic small'
          )}
      >
          + Add Phrase
        </button>
    </div>
  </footer>);

  selectPhrase = (phrase) => {
    this.props.onSelectPhrase(phrase);
  };

  updateFilter = (value) => {
    this.setState({
      filter: value
    });
  };

  isActive = item => item === this.state.selectedLeft;

  render() {
    return (
      <div className="phrases two-panels-menu">
        <MenuWrapper>
          <SearchBox
            onUserInput={this.updateFilter}
          />
          <Menu>
            {this.getGroups()}
          </Menu>
          {this.getLeftFooter()}
        </MenuWrapper>
        {this.getRightPanel()}
      </div>
    );
  }
}
