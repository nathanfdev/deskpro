import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import moment from 'moment';
import Isvg from 'react-inlinesvg';
import { FormattedMessage } from 'react-intl';
import { List } from 'react-virtualized';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import Loader from '@deskpro/react-loader';
import { Modal, Label, Button, ConfirmButton, Input } from '@deskpro/react-components';
import $ from 'jquery';
import { allContentTemplatesSelector, isContentTemplatesLoadedSelector } from '../Selectors/contentTemplates';
import { editContentTemplate, deleteContentTemplate, loadContentTemplates, openContentTemplateEditor } from '../Actions/contentTemplateActions';

@connect(state => ({
  contentTemplates:       allContentTemplatesSelector(state),
  contentTemplatesLoaded: isContentTemplatesLoadedSelector(state),
  agents:                 agentsSelector(state)
}), null, null, { withRef: true })
class ManageContentTemplatesModalContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      height: 0
    };

    window.ManageContentTemplatesModal = this;
  }

  componentDidMount() {
    this.reloadTemplates();
    this.updateWindowDimensions();
    window.addEventListener('resize', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.updateWindowDimensions();
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
  }

  reloadTemplates = () => {
    this.props.dispatch(loadContentTemplates(true));
  };

  updateWindowDimensions = () => {
    this.setState({
      height: window.innerHeight - $('.left-drawer .header').height()
    });
  };

  editTemplate = (contentTemplate) => {
    this.props.dispatch(openContentTemplateEditor(contentTemplate));
  };

  updateTemplate = (editTemplate) => {
    if (!editTemplate) {
      return;
    }

    this.props.dispatch(editContentTemplate(editTemplate.id, editTemplate.data));
  };

  deleteTemplate = (contentTemplate) => {
    this.props.dispatch(deleteContentTemplate(contentTemplate.get('id')));
  };

  render() {
    return (
      <ManageContentTemplatesModal
        {...this.props}
        {...this.state}
        ref={(c) => { this.modal = c; }}
        editTemplate={this.editTemplate}
        updateTemplate={this.updateTemplate}
        deleteTemplate={this.deleteTemplate}
      />
    );
  }
}

class ManageContentTemplatesModal extends React.Component {

  static propTypes = {
    agents:                 PropTypes.object,
    width:                  PropTypes.number,
    height:                 PropTypes.number,
    closeMenu:              PropTypes.func,
    contentTemplates:       PropTypes.object,
    contentTemplatesLoaded: PropTypes.bool,
    editTemplate:           PropTypes.func,
    updateTemplate:         PropTypes.func,
    deleteTemplate:         PropTypes.func
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
    this.state = {
      confirmDeletion: null,
      editTemplate:    null
    };
  }

  componentDidMount() {
    this.offsetTop = this.listRef ? this.listRef.offsetTop : 0;
  }

  componentWillReceiveProps(nextProps) {
    if (!nextProps.contentTemplates.equals(this.props.contentTemplates)) {
      if (this.listRef) {
        this.listRef.forceUpdateGrid();
      }
    }
  }

  rowRenderer({ key, index, style }) {
    const { contentTemplates, agents, editTemplate } = this.props;
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
        editTemplate={editTemplate}
        renameTemplate={this.showEditModal}
        deleteTemplate={this.showDeleteConfirmation}
      />
    );
  }

  showDeleteConfirmation = (contentTemplate) => {
    this.setState({
      confirmDeletion: contentTemplate
    });
  };

  confirmDeletion = () => {
    this.props.deleteTemplate(this.state.confirmDeletion);
    this.setState({
      confirmDeletion: null
    });
  };

  showEditModal = (contentTemplate) => {
    this.setState({
      confirmDeletion: null,
      editTemplate:    {
        id:   contentTemplate.get('id'),
        data: {
          title: contentTemplate.get('title')
        }
      }
    });
  };

  changeEditTemplateTitle = (newTitle) => {
    this.setState({
      editTemplate: {
        ...this.state.editTemplate,
        data: {
          title: newTitle
        }
      }
    });
  };

  updateTemplate = () => {
    this.props.updateTemplate(this.state.editTemplate);
    this.setState({
      editTemplate: null
    });
  };

  cancelModal = () => {
    this.setState({
      confirmDeletion: null,
      editTemplate:    null
    });
  };

  render() {
    const { width, closeMenu, contentTemplates, contentTemplatesLoaded } = this.props;
    const { confirmDeletion, editTemplate } = this.state;

    let { height } = this.props;
    if (isNaN(height)) {
      height = 400;
    }

    const style = {};
    if (width) {
      style.width = width - 5;
    }

    return (
      <div id="snippets__menu" style={style}>
        <div id="content_template__modal">
          {confirmDeletion &&
          <Modal
            title={<FormattedMessage id="agent.general.confirm_deletion" />}
            closeModal={this.cancelModal}
            buttons={
              <div>
                <Button type="secondary" size="large" className="right" onClick={this.cancelModal}>
                  <FormattedMessage id="agent.general.cancel" />
                </Button>
                <ConfirmButton
                  type="secondary"
                  size="large"
                  className="right"
                  onClick={this.confirmDeletion}
                  message={<FormattedMessage id="agent.general.are_you_sure" />}
                >
                  <FormattedMessage id="agent.general.delete" />
                </ConfirmButton>
              </div>
            }
          >
            Do you really want to delete this template? This cannot be undone.
          </Modal>}
          {editTemplate &&
          <Modal
            title={<FormattedMessage id="agent.content_templates.edit_template" />}
            closeModal={this.cancelModal}
            buttons={
              <div>
                <Button type="secondary" size="large" className="right" onClick={this.cancelModal}>
                  <FormattedMessage id="agent.general.cancel" />
                </Button>
                <Button
                  type="secondary"
                  size="large"
                  className="right"
                  onClick={this.updateTemplate}
                >
                  <FormattedMessage id="agent.general.save" />
                </Button>
              </div>
            }
          >
            <Label htmlFor="content_template_title">
              <FormattedMessage id="agent.general.title" />
            </Label>
            <Input
              id="content_template_title"
              type="text"
              value={editTemplate.data.title}
              onChange={this.changeEditTemplateTitle}
            />
          </Modal>}
        </div>

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
            <h1><FormattedMessage id="agent.content_templates.manage_templates" /></h1> <span className="count">({contentTemplates.size})</span>
          </div>
        </div>
        <div className="body">
          <Loader loaded={contentTemplatesLoaded}>
            <List
              className="snippets__list"
              rowCount={contentTemplates.size}
              width={width - 5}
              height={height}
              rowHeight={60}
              rowRenderer={this.rowRenderer}
              noRowsRenderer={ManageContentTemplatesModal.noRowsRenderer}
              overscanRowCount={2}
              ref={(c) => { this.listRef = c; }}
            />
          </Loader>
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
    editTemplate(contentTemplate);
  };

  renameTemplate = (event) => {
    event.preventDefault();

    const { contentTemplate, renameTemplate } = this.props;
    renameTemplate(contentTemplate);
  };

  deleteTemplate = (event) => {
    event.preventDefault();

    const { contentTemplate, deleteTemplate } = this.props;
    deleteTemplate(contentTemplate);
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
