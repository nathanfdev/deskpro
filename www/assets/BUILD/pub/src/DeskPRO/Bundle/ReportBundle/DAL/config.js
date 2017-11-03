import ReportRepository from './Repositories/ReportRepository';

export const repositoriesConfig = {
  Reports:       { type: 'api', url: '/reports', repositoryClass: ReportRepository },
  ReportsLabels: { type: 'api', url: '/reports/labels' }
};

export default repositoriesConfig;
