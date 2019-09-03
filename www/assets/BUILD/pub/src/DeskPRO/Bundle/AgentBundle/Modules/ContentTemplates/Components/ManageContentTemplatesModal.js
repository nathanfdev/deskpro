import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import moment from 'moment';
import Isvg from 'react-inlinesvg';
import { FormattedMessage } from 'react-intl';
import { List } from 'react-virtualized';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allContentTemplatesSelector } from '../Selectors/contentTemplates';
import { loadContentTemplates } from '../Actions/contentTemplateActions';

@connect(state => ({
  contentTemplates: allContentTemplatesSelector(state),
  agents:           agentsSelector(state)
}), null, null, { withRef: true })
class ManageContentTemplatesModalContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  componentDidMount() {
    this.props.dispatch(loadContentTemplates(true));
  }

  render() {
    return (
      <ManageContentTemplatesModal
        {...this.props}
        ref={(c) => { this.modal = c; }}
      />
    );
  }
}

class ManageContentTemplatesModal extends React.Component {

  static propTypes = {
    agents:           PropTypes.object,
    height:           PropTypes.number,
    closeMenu:        PropTypes.func,
    contentTemplates: PropTypes.object
  };

  static noRowsRenderer() {
    return (
      <div className="content-templates_element_wrapper">
        <div className="content-templates-no-results">
          <FormattedMessage id="agent.search.no_results_found" />
        </div>
      </div>
    );
  }

  constructor(props) {
    super(props);
    this.rowRenderer = this.rowRenderer.bind(this);
  }

  componentDidMount() {
    this.offsetTop = this.listRef.offsetTop;
  }

  componentWillReceiveProps(nextProps) {
    if (!nextProps.contentTemplates.equals(this.props.contentTemplates)) {
      this.listRef.forceUpdateGrid();
    }
  }

  editTemplate = () => {};
  renameTemplate = () => {};
  deleteTemplate = () => {};

  rowRenderer({ key, index, style }) {
    const { contentTemplates, agents } = this.props;
    const contentTemplate = contentTemplates.toArray()[index];
    if (!contentTemplate) {
      return null;
    }
    return (
      <ContentTemplateItem
        key={key}
        contentTemplate={contentTemplate}
        style={style}
        agents={agents}
        editTemplate={this.editTemplate}
        renameTemplate={this.renameTemplate}
        deleteTemplate={this.deleteTemplate}
      />
    );
  }

  render() {
    const { closeMenu, contentTemplates } = this.props;
    let height = this.props.height;
    if (isNaN(height)) {
      height = 400;
    }

    return (
      <div id="snippets__menu">
        <div className="header">
          <div className="search">
            <a className="close-icon" onClick={closeMenu}>
              <Isvg
                className="close-icon"
                src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
              />
            </a>
          </div>
          <div className="top">
            <h1><FormattedMessage id="agent.general.manage_templates" /></h1> <span className="count">({contentTemplates.size})</span>
          </div>
          <div className="body">
            <List
              className="snippets__list"
              rowCount={contentTemplates.size}
              width={700}
              height={height}
              rowHeight={60}
              rowRenderer={this.rowRenderer}
              noRowsRenderer={ManageContentTemplatesModal.noRowsRenderer}
              overscanRowCount={2}
              ref={(c) => { this.listRef = c; }}
            />
          </div>
        </div>
      </div>
    );
  }
}

class ContentTemplateItem extends React.Component {

  static propTypes = {
    agents:          PropTypes.object,
    contentTemplate: PropTypes.object,
    style:           PropTypes.object,
    editTemplate:    PropTypes.func,
    renameTemplate:  PropTypes.func,
    deleteTemplate:  PropTypes.func
  };

  editTemplate = (event) => {
    event.preventDefault();

    const { contentTemplate, editTemplate } = this.props;
    editTemplate(contentTemplate.get('id'));
  };

  renameTemplate = (event) => {
    event.preventDefault();

    const { contentTemplate, renameTemplate } = this.props;
    renameTemplate(contentTemplate.get('id'));
  };

  deleteTemplate = (event) => {
    event.preventDefault();

    const { contentTemplate, deleteTemplate } = this.props;
    deleteTemplate(contentTemplate.get('id'));
  };

  render() {
    const { agents, contentTemplate, style } = this.props;
    const agentId = contentTemplate.get('person');

    return (
      <div
        className="content-templates_element_wrapper"
        style={style}
      >
        <div className="content-templates__element">
          <div>
            <span className="title">{contentTemplate.get('title')}</span>
            <div className="author">
              <a data-route={`person:/agent/people/${agentId}`}>
                {agents.getIn([agentId, 'name'])}
              </a>
              <span className="date-created">
                {moment(contentTemplate.get('date_created')).format('YYYY-MM-DD')}
              </span>
            </div>
          </div>
          <div className="manage-content-template-actions">
            <span className="manage-content-template-action">
              <a onClick={this.editTemplate}>Edit</a>
            </span>
            <span className="manage-content-template-action">
              <a onClick={this.renameTemplate}>Rename</a>
            </span>
            <span className="manage-content-template-action">
              <a onClick={this.deleteTemplate}>Delete</a>
            </span>
          </div>
        </div>
      </div>
    );
  }
}

export default ManageContentTemplatesModalContainer;
