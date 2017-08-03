import React, { PropTypes } from 'react';
import { Button } from 'deskpro-components/lib/Components/Buttons';
import { Checkbox, Label, Select } from 'deskpro-components/lib/Components/Forms';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { MassActionsSelect } from './Menus/MassActionsSelect';

class DraftSelect extends React.PureComponent {
  static propTypes = {
    value:    PropTypes.object,
    setValue: PropTypes.func,
  };

  render() {
    const { value, setValue } = this.props;
    const options = [
      { value: true, label: 'Set as draft' },
      { value: true, label: 'Set as published' },
    ];
    return (
      <Select
        value={value}
        options={options}
        clearable={false}
        searchable={false}
        onChange={setValue}
      />
    );
  }
}

export default class MassActions extends React.Component {
  static propTypes = {
    action: PropTypes.string.isRequired,
    close:  PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      actionValue: null
    };
    this.setActionValue = this.setActionValue.bind(this);
    this.getAction = this.getAction.bind(this);
    this.runAction = this.runAction.bind(this);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.action !== this.props.action) {
      this.setState({
        actionValue: null
      });
    }
  }

  setActionValue = (value) => {
    this.setState({
      actionValue: value
    });
  };


  getAction() {
    switch (this.props.action) {
      case 'draft':
        return (<DraftSelect
          value={this.state.actionValue}
          setValue={this.setActionValue}
        />);
      case 'export':
        return <span />;
      default:
        return <span>Unknown action</span>;
    }
  }

  runAction() {
    console.log(this.state.actionValue);
  }

  render() {
    const { action, close } = this.props;
    const actions = MassActionsSelect.getActions();
    console.log(actions);
    return (
      <div>
        <div className="snippets__mass-actions--content">
          <a href="#close" className="close-cross" onClick={close}>
            {agentPhrases.get('agent.general.close_lc')}
          </a>
          <Label>
            {actions.find(a => a.value === action).text}
          </Label>
          {this.getAction()}
          <Button
            size="medium"
            onClick={this.runAction}
          >
            {this.props.action === 'export' ?
              agentPhrases.get('agent.general.export')
              : agentPhrases.get('agent.snippets.save_changes')
            }
          </Button>
        </div>
        <Checkbox>{agentPhrases.get('agent.general.select_all')}</Checkbox>
      </div>

    );
  }
}
