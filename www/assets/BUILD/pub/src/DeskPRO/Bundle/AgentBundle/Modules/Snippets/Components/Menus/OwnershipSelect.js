import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { CustomSelect, Radio, Checkbox } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

@connect(state => ({
  agentTeams: allSelectorFactory('AgentTeam')(state)
}))
export class OwnershipSelectContainer extends React.Component {
  static propTypes = {
    agentTeams:        PropTypes.object,
    selectedTeams:     PropTypes.object.isRequired,
    isOwnershipGlobal: PropTypes.bool,
    onChange:          PropTypes.func,
  };

  render() {
    const { agentTeams, onChange, selectedTeams, isOwnershipGlobal } = this.props;
    return (
      <OwnershipSelect
        agentTeams={agentTeams}
        selectedTeams={selectedTeams}
        isOwnershipGlobal={isOwnershipGlobal}
        onChange={onChange}
      />
    );
  }
}
export class OwnershipSelect extends React.Component {
  static propTypes = {
    agentTeams:        PropTypes.object,
    selectedTeams:     PropTypes.object,
    isOwnershipGlobal: PropTypes.bool,
    onChange:          PropTypes.func,
  };

  constructor(props) {
    super(props);
    let radio = '';
    if (this.props.isOwnershipGlobal) {
      radio = 'everyone';
    } else if (this.props.selectedTeams.size) {
      radio = 'specific';
    } else {
      radio = 'me';
    }
    this.state = {
      radio
    };
  }

  shouldComponentUpdate(nextProps) {
    if (nextProps.agentTeams !== this.props.agentTeams) {
      return true;
    }
    if (nextProps.isOwnershipGlobal !== this.props.isOwnershipGlobal) {
      return true;
    }
    return nextProps.selectedTeams !== this.props.selectedTeams;
  }

  onRadioChange = (checked, value) => {
    this.setState({
      radio: value
    });
    if (value === 'everyone') {
      this.props.onChange(new Set(), true);
    } else {
      this.props.onChange(new Set(), false);
    }
  };

  onCheckboxChange = (checked, value) => {
    const { selectedTeams } = this.props;
    if (checked) {
      selectedTeams.add(value);
    } else {
      selectedTeams.delete(value);
    }
    this.props.onChange(selectedTeams, false);
    this.forceUpdate();
  };

  getSpecific = () => {
    const { selectedTeams } = this.props;
    const teams = [];
    this.props.agentTeams.forEach((team) => {
      teams.push(
        <ListElement key={team.get('id')}>
          <Checkbox
            value={team.get('id')}
            onChange={this.onCheckboxChange}
            checked={selectedTeams.has(team.get('id'))}
          >
            {team.get('name')}
          </Checkbox>
        </ListElement>
      );
    });
    return (
      <List>
        {teams}
      </List>
    );
  };

  inputRenderer = () => {
    const { selectedTeams, isOwnershipGlobal, agentTeams } = this.props;
    if (selectedTeams.size === 0) {
      return isOwnershipGlobal ? agentPhrases.get('agent.general.everyone') : agentPhrases.get('agent.general.just_me');
    } else if (selectedTeams.size <= 3) {
      return agentTeams.filter(t => selectedTeams.has(t.get('id')))
        .map(o => o.get('name')).toArray().join(', ');
    }
    return `${selectedTeams.size} ${agentPhrases.get('agent.general.teams').toLowerCase()}`;
  };

  render() {
    const { radio } = this.state;
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
      >
        <List>
          <ListElement>
            <Radio
              name="ownership"
              value="me"
              onChange={this.onRadioChange}
              checked={radio === 'me'}
            >
              {agentPhrases.get('agent.general.just_me')}
            </Radio>
          </ListElement>
          <ListElement>
            <Radio
              name="ownership"
              value="everyone"
              onChange={this.onRadioChange}
              checked={radio === 'everyone'}
            >
              {agentPhrases.get('agent.general.everyone')}
            </Radio>
          </ListElement>
          <ListElement>
            <Radio
              name="ownership"
              value="specific"
              onChange={this.onRadioChange}
              checked={radio === 'specific'}
            >
              {agentPhrases.get('agent.snippets.specific_teams')}
            </Radio>
          </ListElement>
          { this.state.radio === 'specific' ? this.getSpecific() : null}
        </List>
      </CustomSelect>
    );
  }
}
