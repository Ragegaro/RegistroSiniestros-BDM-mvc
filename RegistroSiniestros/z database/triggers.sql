USE bdm_aseguradora;

-- El historial de estatus se registra desde SiniestroM::cambiarEstatus().
-- La asignacion de ajustador es manual desde el menu del supervisor.
-- Se retira el disparador antiguo sin modificar asignaciones existentes.
DROP TRIGGER IF EXISTS T_asignar_Ajust;
DROP TRIGGER IF EXISTS T_historial_estatus;
